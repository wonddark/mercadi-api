<?php

namespace App\Repository;

use App\Entity\Bid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bid>
 *
 * @method Bid|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bid|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bid[]    findAll()
 * @method Bid[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BidRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bid::class);
    }

    public function save(Bid $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Bid $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getHighestPerOffer(string $offerId)
    {
        $query = $this->createQueryBuilder('b')
            ->innerJoin("b.offer", "o")
            ->where("o.id = :offer_id")
            ->setParameter("offer_id", $offerId, "uuid")
            ->orderBy("b.quantity", "DESC")
            ->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }
}
