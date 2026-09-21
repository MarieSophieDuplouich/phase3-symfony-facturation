<?php

namespace App\Controller;

use App\Enum\Status;
use App\Repository\ClientRepository;
use App\Repository\InvoiceRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        InvoiceRepository $invoiceRepository,
        ClientRepository $clientRepository,
        ProductRepository $productRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $totalRevenue = (float) $invoiceRepository->createQueryBuilder('i')
            ->select('COALESCE(SUM(i.totalTtc), 0)')
            ->andWhere('i.user = :user')
            ->andWhere('i.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', Status::PAID)
            ->getQuery()
            ->getSingleScalarResult();

        $totalPendingInvoices = (int) $invoiceRepository->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->andWhere('i.user = :user')
            ->andWhere('i.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', Status::PENDING_PAYMENT)
            ->getQuery()
            ->getSingleScalarResult();

        $totalClients = count($clientRepository->findBy(['user' => $user]));
        $totalProducts = count($productRepository->findBy(['user' => $user]));

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'totalRevenue' => $totalRevenue,
            'totalPendingInvoices' => $totalPendingInvoices,
            'totalClients' => $totalClients,
            'totalProducts' => $totalProducts,
        ]);
    }
}