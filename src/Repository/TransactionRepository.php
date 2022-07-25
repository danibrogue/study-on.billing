<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Util\Exception;

/**
 * @extends ServiceEntityRepository<Transaction>
 *
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function add(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findSomeBy(array $filters, $user, EntityManagerInterface $em): array
    {
        $query = $this->createQueryBuilder('t');
        if (!$filters) {
            throw new \Exception('Фильтры не установлены');
        }
        if (isset($filters['type'])) {
            $query
                ->andWhere('t.type = :type')
                ->setParameter('type', $filters['type']);
        }

        if (isset($filters['course_code'])) {
            $course = $em->getRepository(Course::class)->findOneBy([
                'code' => $filters['course_code']
            ]);
            $query
                ->andWhere('t.course = :course')
                ->setParameter('course', $course);
        }
        if (isset($filters['skip_expired'])) {
            $date = new \DateTimeImmutable();
            $query
                ->andWhere('t.expireTime > :date OR t.expireTime IS NULL')
                ->setParameter('date', $date);
        }
        return $query
            ->andWhere('t.customer = :customer')
            ->setParameter('customer', $user)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Transaction[] Returns an array of Transaction objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Transaction
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
