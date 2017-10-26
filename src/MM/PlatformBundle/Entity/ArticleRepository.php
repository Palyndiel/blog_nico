<?php
// src/MM/PlatformBundle/Entity/ArticleRepository.php

namespace MM\PlatformBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ArticleRepository extends EntityRepository
{
  public function myFindAll()
  {
    return $this
    ->createQueryBuilder('a')
    ->getQuery()
    ->getResult()
    ;
  }

  public function whereCurrentYear(QueryBuilder $qb)
  {
    $qb
      ->andWhere('a.date BETWEEN :start AND :end')
      ->setParameter('start', new \Datetime(date('Y').'-01-01'))  // Date entre le 1er janvier de cette année
      ->setParameter('end',   new \Datetime(date('Y').'-12-31'))  // Et le 31 décembre de cette année
    ;
  }
}