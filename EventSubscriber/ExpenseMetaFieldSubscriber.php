<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\EventSubscriber;

use KimaiPlugin\ExpensesBundle\Entity\Expense;
use KimaiPlugin\ExpensesBundle\Entity\ExpenseMeta;
use KimaiPlugin\ExpensesBundle\Event\ExpenseMetaDefinitionEvent;
use KimaiPlugin\ExpensesBundle\Event\ExpenseMetaDisplayEvent;
use KimaiPlugin\MetaFieldsBundle\MetaFieldsRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ExpenseMetaFieldSubscriber extends AbstractCustomFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        if (!class_exists(MetaFieldsRegistry::EXPENSE_CLASS)) {
            return [];
        }

        return [
            ExpenseMetaDefinitionEvent::class => ['loadExpenseMeta', 200],
            ExpenseMetaDisplayEvent::class => ['loadExpenseFields', 200],
        ];
    }

    public function loadExpenseMeta(ExpenseMetaDefinitionEvent $event)
    {
        /** @var Expense $entity */
        $entity = $event->getEntity();

        if (\get_class($entity) !== MetaFieldsRegistry::EXPENSE_CLASS) {
            $this->logger->error(
                sprintf('Wrong entity given, expected %s but received %s', MetaFieldsRegistry::EXPENSE_CLASS, \get_class($entity))
            );

            return;
        }

        $rules = $this->getRulesForEntityType(MetaFieldsRegistry::EXPENSE_ENTITY);

        if (empty($rules)) {
            return;
        }

        foreach ($rules as $rule) {
            $entity->setMetaField($this->getMetaDefinitionForRule(new ExpenseMeta(), $rule));
        }
    }

    public function loadExpenseFields(ExpenseMetaDisplayEvent $event)
    {
        $rules = $this->getRulesForEntityType(MetaFieldsRegistry::EXPENSE_ENTITY);

        if (empty($rules)) {
            return;
        }

        foreach ($rules as $rule) {
            if (!$rule->isVisible()) {
                continue;
            }

            $event->addField($this->getMetaDefinitionForRule(new ExpenseMeta(), $rule));
        }
    }
}
