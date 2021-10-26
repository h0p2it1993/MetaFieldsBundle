<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\EventSubscriber;

use App\Entity\MetaTableTypeInterface;
use KimaiPlugin\MetaFieldsBundle\Entity\MetaFieldRule;
use KimaiPlugin\MetaFieldsBundle\Repository\MetaFieldRuleRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

abstract class AbstractCustomFieldSubscriber
{
    /**
     * @var MetaFieldRuleRepository
     */
    private $repository;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $security;

    public function __construct(MetaFieldRuleRepository $repository, AuthorizationCheckerInterface $security, LoggerInterface $logger = null)
    {
        $this->repository = $repository;
        $this->security = $security;
        $this->logger = $logger;
    }

    protected function canSeeRule(MetaFieldRule $rule)
    {
        if (empty($rule->getPermission())) {
            return true;
        }

        return $this->security->isGranted($rule->getPermission());
    }

    /**
     * @param string $entityType
     * @return MetaFieldRule[]
     */
    protected function getRulesForEntityType(string $entityType): array
    {
        $rules = [];

        try {
            $rules = $this->repository->findRulesForEntityType($entityType);
        } catch (\Exception $ex) {
            $this->logger->error(
                sprintf('Failed loading custom field rules: %s', $ex->getMessage())
            );
        }

        return $rules;
    }

    protected function getChoicesFromRule(MetaFieldRule $rule): array
    {
        $choices = [];

        foreach (explode(',', $rule->getValue()) as $choice) {
            $choice = trim($choice);
            $choice = explode('|', $choice);
            if (\count($choice) === 1) {
                $choices[$choice[0]] = $choice[0];
            } else {
                $choices[$choice[1]] = $choice[0];
            }
        }

        return $choices;
    }

    protected function getMetaDefinitionForRule(MetaTableTypeInterface $type, MetaFieldRule $rule): MetaTableTypeInterface
    {
        $options = ['label' => $rule->getLabel()];

        $value = $rule->getValue();

        if ($rule->getType() === MetaFieldRule::CHOICE_TYPE) {
            $options['choices'] = $this->getChoicesFromRule($rule);
            $options['search'] = false;
            $value = null;
        } elseif ($rule->getType() === MetaFieldRule::CHOICE_TYPE_MULTIPLE) {
            $options['choices'] = $this->getChoicesFromRule($rule);
            $options['multiple'] = true;
            $options['expanded'] = false;
            $options['search'] = false;
            $value = null;
        } elseif ($rule->getType() === 'boolean') {
            $value = (bool) $value;
        }

        if (!empty($rule->getHelp())) {
            $options['help'] = $rule->getHelp();
        }

        $type
            ->setName($rule->getName())
            ->setType($rule->getMappedFieldType())
            ->setIsVisible($rule->isVisible())
            ->setIsRequired($rule->isRequired())
            ->setLabel($rule->getLabel())
            ->setOptions($options)
            ->setValue($value)
            ->setOrder($rule->getWeight())
        ;

        return $type;
    }
}
