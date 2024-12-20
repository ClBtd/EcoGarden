<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

        /**
         * @return Article[] Retourne un tableau d'articles correspondant à un mois 
         */
        public function findMonthArticles($month): array
        {
            return $this->createQueryBuilder('a')
                ->andWhere('JSON_CONTAINS(a.months, :month) = 1')
                ->setParameter('month', json_encode([$month]))
                ->getQuery()
                ->getResult()
            ;
        }

    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
