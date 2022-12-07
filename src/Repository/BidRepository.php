<?php

namespace App\Repository;

use ApiPlatform\Doctrine\Orm\Paginator;
use App\Entity\Bid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
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

    /**
     * @throws QueryException
     */
    public function getUserBids(string $userId, int $page, int $itemsPerPage): Paginator
    {
        $firstResult = ($page - 1) * $itemsPerPage;
        $query = $this->createQueryBuilder("b")
            ->innerJoin("b.user", "user")
            ->innerJoin("b.offer", "o")
            ->innerJoin("o.user", "u")
            ->addSelect("o")
            ->where("user.id = :userId")
            ->andWhere("u.id != :userId")
            ->setParameter("userId", $userId, "uuid");

        $criteria = Criteria::create()
            ->setFirstResult($firstResult)
            ->setMaxResults($itemsPerPage);
        $query->addCriteria($criteria);

        $doctrinePaginator = new DoctrinePaginator($query);

        return new Paginator($doctrinePaginator);
    }
}
