<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use KimaiPlugin\MetaFieldsBundle\Entity\MetaFieldRule;
use KimaiPlugin\MetaFieldsBundle\MetaFieldsRegistry;

/**
 * @extends \Doctrine\ORM\EntityRepository<MetaFieldRule>
 */
final class MetaFieldRuleRepository extends EntityRepository
{
    public function saveRule(MetaFieldRule $rule)
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($rule);
        $entityManager->flush();
    }

    public function deleteRule(MetaFieldRule $rule)
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($rule);
        $entityManager->flush();
    }

    public function countRuleUsages(MetaFieldRule $rule): int
    {
        $repository = null;

        $repository = MetaFieldsRegistry::mapEntityTypeToMetaClass($rule->getEntityType());

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('COUNT(meta.id) as metaCount')
            ->from($repository, 'meta')
            ->where('meta.name = :name')
            ->setParameter('name', $rule->getName())
            ->andWhere($qb->expr()->isNotNull('meta.value'))
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return MetaFieldRule[]
     */
    public function getRules(): array
    {
        return $this->findBy([], ['weight' => Criteria::ASC]);
    }

    /**
     * @param string $entityType
     * @return MetaFieldRule[]
     */
    public function findRulesForEntityType(string $entityType): array
    {
        return $this->findBy(['entityType' => $entityType], ['weight' => Criteria::ASC]);
    }
}
