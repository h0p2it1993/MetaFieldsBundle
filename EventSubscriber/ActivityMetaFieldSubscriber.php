<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\EventSubscriber;

use App\Entity\ActivityMeta;
use App\Entity\Customer;
use App\Entity\Project;
use App\Event\ActivityMetaDefinitionEvent;
use App\Event\ActivityMetaDisplayEvent;
use KimaiPlugin\MetaFieldsBundle\Entity\MetaFieldRule;
use KimaiPlugin\MetaFieldsBundle\MetaFieldsRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ActivityMetaFieldSubscriber extends AbstractCustomFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ActivityMetaDefinitionEvent::class => ['loadActivityMeta', 200],
            ActivityMetaDisplayEvent::class => ['loadActivityFields', 200],
        ];
    }

    private function isRuleForActivity(MetaFieldRule $rule, ?Customer $customer, ?Project $project): bool
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

        if (null !== $rule->getProject() &&
            (
                null === $project ||
                $rule->getProject()->getId() !== $project->getId()
            )
        ) {
            return false;
        }

        return true;
    }

    public function loadActivityMeta(ActivityMetaDefinitionEvent $event)
    {
        $entity = $event->getEntity();
        $rules = $this->getRulesForEntityType(MetaFieldsRegistry::ACTIVITY_ENTITY);

        if (empty($rules)) {
            return;
        }

        foreach ($rules as $rule) {
            $customer = $entity->getProject() !== null ? $entity->getProject()->getCustomer() : null;
            if (!$this->isRuleForActivity($rule, $customer, $entity->getProject())) {
                $meta = $entity->getMetaField($rule->getName());
                if (null !== $meta) {
                    $meta->setIsVisible(false);
                }
                continue;
            }

            $entity->setMetaField(
                $this->getMetaDefinitionForRule(new ActivityMeta(), $rule)
            );
        }
    }

    public function loadActivityFields(ActivityMetaDisplayEvent $event)
    {
        $rules = $this->getRulesForEntityType(MetaFieldsRegistry::ACTIVITY_ENTITY);

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

            $project = $query->getProject();
            if (\is_int($project)) {
                continue;
            }

            if (!$this->isRuleForActivity($rule, $customer, $project)) {
                continue;
            }

            $event->addField(
                $this->getMetaDefinitionForRule(new ActivityMeta(), $rule)
            );
        }
    }
}
