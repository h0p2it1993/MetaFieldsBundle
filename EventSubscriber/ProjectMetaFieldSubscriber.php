<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\EventSubscriber;

use App\Entity\Customer;
use App\Entity\ProjectMeta;
use App\Event\ProjectMetaDefinitionEvent;
use App\Event\ProjectMetaDisplayEvent;
use KimaiPlugin\MetaFieldsBundle\Entity\MetaFieldRule;
use KimaiPlugin\MetaFieldsBundle\MetaFieldsRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectMetaFieldSubscriber extends AbstractCustomFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProjectMetaDefinitionEvent::class => ['loadProjectMeta', 200],
            ProjectMetaDisplayEvent::class => ['loadProjectFields', 200],
        ];
    }

    private function isRuleForProject(MetaFieldRule $rule, ?Customer $customer): bool
    {
        if (!$this->canSeeRule($rule)) {
            return false;
        }

        if (null !== $rule->getCustomer() &&
            (
                null === $customer ||
                $rule->getCustomer()->getId() !== $customer->getId()
            )
        ) {
            return false;
        }

        return true;
    }

    public function loadProjectMeta(ProjectMetaDefinitionEvent $event)
    {
        $entity = $event->getEntity();
        $rules = $this->getRulesForEntityType(MetaFieldsRegistry::PROJECT_ENTITY);

        if (empty($rules)) {
            return;
        }

        foreach ($rules as $rule) {
            if (!$this->isRuleForProject($rule, $entity->getCustomer())) {
                $meta = $entity->getMetaField($rule->getName());
                if (null !== $meta) {
                    $meta->setIsVisible(false);
                }
                continue;
            }

            $entity->setMetaField(
                $this->getMetaDefinitionForRule(new ProjectMeta(), $rule)
            );
        }
    }

    public function loadProjectFields(ProjectMetaDisplayEvent $event)
    {
        $rules = $this->getRulesForEntityType(MetaFieldsRegistry::PROJECT_ENTITY);

        if (empty($rules)) {
            return;
        }

        $query = $event->getQuery();

        foreach ($rules as $rule) {
            if (!$rule->isVisible()) {
                continue;
            }

            // FIXME
            $customer = $query->getCustomer();
            if (\is_int($customer)) {
                continue;
            }

            if (!$this->isRuleForProject($rule, $customer)) {
                continue;
            }

            $event->addField(
                $this->getMetaDefinitionForRule(new ProjectMeta(), $rule)
            );
        }
    }
}
