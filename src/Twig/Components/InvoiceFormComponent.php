<?php
namespace App\Twig\Components;

use App\Entity\InvoiceItem;
use App\Entity\Invoice;
use App\Enum\Status;
use App\Repository\ClientRepository;
use App\Repository\InvoiceRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class InvoiceFormComponent extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?int $clientId = null;

    #[LiveProp(writable: true)]
    public string $date = '';

    #[LiveProp(writable: true)]
    public ?int $selectedProductId = null;

    #[LiveProp(writable: true)]
    public int $quantity = 1;

    #[LiveProp(writable: true)]
    public array $lines = [];

    public function __construct(
        private ProductRepository $productRepository,
        private ClientRepository $clientRepository,
        private InvoiceRepository $invoiceRepository,
        private EntityManagerInterface $em,
        private Security $security,
    ) {
        $this->date = (new \DateTime())->format('Y-m-d');
    }

    public function getClients(): array
    {
        return $this->clientRepository->findBy(['user' => $this->security->getUser()]);
    }

    public function getProducts(): array
    {
        return $this->productRepository->findBy(['user' => $this->security->getUser()]);
    }

    public function getTotal(): float
    {
        return array_sum(array_map(fn($l) => $l['quantity'] * $l['unitPrice'], $this->lines));
    }

    #[LiveAction]
    public function addLine(): void
    {
        if (!$this->selectedProductId) return;

        $product = $this->productRepository->find($this->selectedProductId);
        if (!$product) return;

        $this->lines[] = [
            'productId'   => $product->getId(),
            'name'        => $product->getName(),
            'description' => $product->getDescription() ?? '',
            'quantity'    => $this->quantity,
            'unitPrice'   => $product->getPrice(),
        ];

        $this->selectedProductId = null;
        $this->quantity = 1;
    }

    #[LiveAction]
    public function removeLine(int $index): void
    {
        array_splice($this->lines, $index, 1);
    }

    #[LiveAction]
    public function save(string $action = 'draft'): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        if (!$this->clientId || empty($this->lines)) {
            return $this->redirectToRoute('app_invoice_new');
        }

        $invoice = new Invoice();
        $invoice->setUser($this->security->getUser());
        $invoice->setNumber($this->invoiceRepository->generateNextNumber());
        $invoice->setStatus($action === 'validate' ? Status::PENDING_PAYMENT : Status::DRAFT);
        $invoice->setCreatedAt(new \DateTimeImmutable($this->date));

        $client = $this->clientRepository->find($this->clientId);
        $invoice->setClient($client);

        foreach ($this->lines as $lineData) {
            $product = $this->productRepository->find($lineData['productId']);
            if (!$product) continue;

            $item = new InvoiceItem();
            $item->setProduct($product);
            $item->setQuantity($lineData['quantity']);
            $invoice->addItem($item);
            $this->em->persist($item);
        }

        $invoice->computeAndSaveTotalTtc();
        $this->em->persist($invoice);
        $this->em->flush();

        return $this->redirectToRoute('app_invoice_index');
    }
}