<?php

namespace App\Repository;

use App\Entity\Family;
use App\Entity\Technique;
use App\Search\SearchNormalizer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Technique> */
class TechniqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Technique::class);
    }

    /**
     * @return list<Technique> ordered by category, then Kodokan order
     */
    public function search(?string $query = null, ?Family $family = null, ?string $categorySlug = null, bool $withMotionOnly = false): array
    {
        $qb = $this->createQueryBuilder('t')
            ->addSelect('c', 'm')
            ->join('t.category', 'c')
            ->leftJoin('t.motion', 'm')
            ->orderBy('c.position', 'ASC')
            ->addOrderBy('t.position', 'ASC');

        $needle = null !== $query ? SearchNormalizer::normalize($query) : '';
        if ('' !== $needle) {
            $qb->andWhere('t.searchText LIKE :q')
                ->setParameter('q', '%'.addcslashes($needle, '%_\\').'%');
        }
        if (null !== $family) {
            $qb->andWhere('c.family = :family')->setParameter('family', $family);
        }
        if (null !== $categorySlug && '' !== $categorySlug) {
            $qb->andWhere('c.slug = :cat')->setParameter('cat', $categorySlug);
        }
        if ($withMotionOnly) {
            $qb->andWhere('m.id IS NOT NULL');
        }

        return $qb->getQuery()->getResult();
    }

    public function findOneBySlugWithMotion(string $slug): ?Technique
    {
        return $this->createQueryBuilder('t')
            ->addSelect('c', 'm')
            ->join('t.category', 'c')
            ->leftJoin('t.motion', 'm')
            ->where('t.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
