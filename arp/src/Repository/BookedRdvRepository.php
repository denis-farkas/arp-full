<?php


namespace App\Repository;

use App\Entity\BookedRdv;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookedRdv>
 */
class BookedRdvRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookedRdv::class);
    }

    public function findByBookingToken(string $token): ?BookedRdv
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.bookingToken = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findConflictingBookings(\DateTimeImmutable $beginAt, int $duration): array
    {
        $endAt = $beginAt->modify("+{$duration} minutes");
        
        return $this->createQueryBuilder('b')
            ->join('b.rdv', 'r')
            ->where('b.beginAt < :endAt')
            ->andWhere('DATE_ADD(b.beginAt, r.duration, \'MINUTE\') > :beginAt')
            ->setParameter('beginAt', $beginAt)
            ->setParameter('endAt', $endAt)
            ->getQuery()
            ->getResult();
    }
}