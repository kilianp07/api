<?php

namespace App\Repository;

use App\Entity\Recette;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recette>
 *
 * @method Recette|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recette|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recette[]    findAll()
 * @method Recette[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recette::class);
    }

    public function save(Recette $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Recette $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findWithPagination($page, $limit){
        $qb = $this->createQueryBuilder('c')
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit)
            ->where('c.status = true');

            return $qb->getQuery()->getResult();


    }

    public function findBetweenDates(DateTimeImmutable $startDate, DateTimeImmutable $endDate, int $pages, int $limit)
    {
       $startDateTime = $startDate ? $startDate: new \DateTimeImmutable('now');

       $qb = $this->createQueryBuilder("c");
       $qb->add(
        'where',
        $qb->expr()->orX(
        $qb->expr()->andX(
            $qb->expr()->gte("c.dataStart", ":startdate"),
            $qb->expr()->lte("c.dataEnd", ":enddate")
        ),
        $qb->expr()->orX(
            $qb->expr()->gte("c.dataStart", ":startdate"),
            $qb->expr()->lte("c.dataEnd", ":enddate")
          )
        )
        );
    }

    // This method return a recette with a recette by an ingredient
    public function getRecetteByIngredient(string $name){
        $qb = $this->createQueryBuilder('c')
            ->where('c.status = true')
            ->innerJoin('c.ingredients', 'i')
            ->andWhere('i.name = :name')
            ->setParameter('name', $name);

            return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Recette[] Returns an array of Recette objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Recette
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
