<?php

namespace App\Repository;

use ApiPlatform\Doctrine\Orm\Paginator;
use App\Entity\Bidding;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bidding>
 *
 * @method Bidding|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bidding|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bidding[]    findAll()
 * @method Bidding[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BiddingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bidding::class);
    }

    public function save(Bidding $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Bidding $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getHighestPerItem(string $itemId)
    {
        $query = $this->createQueryBuilder('b')
            ->innerJoin("b.item", "item")
            ->where("item.id = :item_id")
            ->setParameter("item_id", $itemId, "uuid")
            ->orderBy("b.quantity", "DESC")
            ->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @throws QueryException
     */
    public function getUserBids(string $userId, int $page, int $itemsPerPage, bool $openStatus = null): Paginator
    {
        $firstResult = ($page - 1) * $itemsPerPage;
        $query = $this->createQueryBuilder("b")
            ->innerJoin("b.user", "user")
            ->innerJoin("b.item", "item")
            ->innerJoin("item.user", "u")
            ->addSelect("item")
            ->where("user.id = :userId")
            ->andWhere("u.id != :userId")
            ->setParameter("userId", $userId, "uuid");

        if ($openStatus !== null) {
            $query
                ->andWhere("item.open = :openStatus")
                ->setParameter("openStatus", $openStatus);
        }

        $criteria = Criteria::create()
            ->setFirstResult($firstResult)
            ->setMaxResults($itemsPerPage);
        $query->addCriteria($criteria);

        $doctrinePaginator = new DoctrinePaginator($query);

        return new Paginator($doctrinePaginator);
    }
}
