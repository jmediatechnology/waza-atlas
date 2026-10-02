<?php

namespace App\Repository;

use App\Entity\WazaCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<WazaCategory> */
class WazaCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WazaCategory::class);
    }

    /** @return list<array{category: WazaCategory, techniques: int, withMotion: int}> */
    public function findAllWithCounts(): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select('c AS category', 'COUNT(t.id) AS techniques', 'COUNT(m.id) AS withMotion')
            ->leftJoin('c.techniques', 't')
            ->leftJoin('t.motion', 'm')
            ->groupBy('c.id')
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(static fn (array $r) => [
            'category' => $r['category'],
            'techniques' => (int) $r['techniques'],
            'withMotion' => (int) $r['withMotion'],
        ], $rows);
    }
}
