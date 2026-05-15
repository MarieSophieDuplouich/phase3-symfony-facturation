<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Enum\Status;
use App\Form\InvoiceType;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invoice')]
final class InvoiceController extends AbstractController
{

    #[Route(name: 'app_invoice_index', methods: ['GET'])]
    public function index(Request $request, InvoiceRepository $invoiceRepository): Response
    {
        $filter   = $request->query->get('status', 'all');
        $criteria = ['user' => $this->getUser()];

        if ($filter !== 'all') {
            $criteria['status'] = Status::from($filter);
        }

        return $this->render('invoice/index.html.twig', [
            'invoices' => $invoiceRepository->findBy($criteria, ['createdAt' => 'DESC']),
            'filter'   => $filter,
        ]);
    }

    #[Route('/new', name: 'app_invoice_new', methods: ['GET'])]
    public function new(): Response
    {
        return $this->render('invoice/new.html.twig');
    }

    #[Route('/{id}', name: 'app_invoice_show', methods: ['GET'])]
    public function show(Invoice $invoice): Response
    {
        if ($invoice->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('invoice/show.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_invoice_edit', methods: ['GET'])]
    public function edit(Invoice $invoice): Response
    {
        if ($invoice->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if (!$invoice->isDraft()) {
            $this->addFlash('error', 'Seules les factures en brouillon peuvent être modifiées.');
            return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
        }

        return $this->render('invoice/edit.html.twig', [
            'invoice' => $invoice,
        ]);
    }
    #[Route('/{id}/validate', name: 'app_invoice_validate', methods: ['POST'])]
    public function validate(Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        if ($invoice->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($invoice->isDraft()) {
            $invoice->setStatus(Status::PENDING_PAYMENT);
            $entityManager->flush();
            $this->addFlash('success', 'Facture validée.');
        }

        return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
    }

    #[Route('/{id}/paid', name: 'app_invoice_paid', methods: ['POST'])]
    public function markAsPaid(Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        if ($invoice->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($invoice->isPending()) {
            $invoice->setStatus(Status::PAID);
            $entityManager->flush();
            $this->addFlash('success', 'Facture marquée comme payée.');
        }

        return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
    }

    #[Route('/{id}', name: 'app_invoice_delete', methods: ['POST'])]
    public function delete(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        if ($invoice->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if (!$invoice->isDeletable()) {
            $this->addFlash('error', 'Seules les factures en brouillon peuvent être supprimées.');
            return $this->redirectToRoute('app_invoice_index');
        }

        if ($this->isCsrfTokenValid('delete' . $invoice->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($invoice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
    }
}
