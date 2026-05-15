<?php

namespace App\Repository;

use App\Entity\Invoice;
use App\Entity\User;
use App\Enum\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function generateNextNumber(): string
    {
        $now        = new \DateTime();
        $year       = $now->format('Y');
        $month      = $now->format('m');
        $day        = $now->format('d');
        $startMonth = new \DateTimeImmutable($year . '-' . $month . '-01 00:00:00');
        $endMonth   = $startMonth->modify('last day of this month')->setTime(23, 59, 59);

        $count = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.createdAt >= :start')
            ->andWhere('i.createdAt <= :end')
            ->setParameter('start', $startMonth)
            ->setParameter('end', $endMonth)
            ->getQuery()
            ->getSingleScalarResult();

        return sprintf('FACT-%s%s%s-%d', $year, $month, $day, (int) $count + 1);
    }
}
