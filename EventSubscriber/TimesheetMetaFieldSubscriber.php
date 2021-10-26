<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\EventSubscriber;

use App\Entity\Activity;
use App\Entity\Customer;
use App\Entity\Project;
use App\Entity\TimesheetMeta;
use App\Event\TimesheetMetaDefinitionEvent;
use App\Event\TimesheetMetaDisplayEvent;
use KimaiPlugin\MetaFieldsBundle\Entity\MetaFieldRule;
use KimaiPlugin\MetaFieldsBundle\MetaFieldsRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class TimesheetMetaFieldSubscriber extends AbstractCustomFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            TimesheetMetaDefinitionEvent::class => ['loadTimesheetMeta', 200],
            TimesheetMetaDisplayEvent::class => ['loadTimesheetFields', 200],
        ];
    }

    private function isRuleForTimesheet(MetaFieldRule $rule, ?Customer $customer, ?Project $project, ?Activity $activity): bool
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

        if (null !== $rule->getActivity() &&
            (
                null === $activity ||
                $rule->getActivity()->getId() !== $activity->getId()
            )
        ) {
            return false;
        }

        return true;
    }

    public function loadTimesheetMeta(TimesheetMetaDefinitionEvent $event)
    {
        $entity = $event->getEntity();
        $rules = $this->getRulesForEntityType(MetaFieldsRegistry::TIMESHEET_ENTITY);

        if (empty($rules)) {
            return;
        }

        foreach ($rules as $rule) {
            $customer = $entity->getProject() !== null ? $entity->getProject()->getCustomer() : null;

            if (!$this->isRuleForTimesheet($rule, $customer, $entity->getProject(), $entity->getActivity())) {
                continue;
            }

            $event->getEntity()->setMetaField(
                $this->getMetaDefinitionForRule(new TimesheetMeta(), $rule)
            );
        }
    }

    public function loadTimesheetFields(TimesheetMetaDisplayEvent $event)
    {
        $rules = $this->getRulesForEntityType(MetaFieldsRegistry::TIMESHEET_ENTITY);

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

            $activity = $query->getActivity();
            if (\is_int($activity)) {
                continue;
            }

            if (!$this->isRuleForTimesheet($rule, $customer, $project, $activity)) {
                continue;
            }

            $event->addField(
                $this->getMetaDefinitionForRule(new TimesheetMeta(), $rule)
            );
        }
    }
}
