<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\EventSubscriber;

use App\Entity\CustomerMeta;
use App\Event\CustomerMetaDefinitionEvent;
use App\Event\CustomerMetaDisplayEvent;
use KimaiPlugin\MetaFieldsBundle\MetaFieldsRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CustomerMetaFieldSubscriber extends AbstractCustomFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CustomerMetaDefinitionEvent::class => ['loadCustomerMeta', 200],
            CustomerMetaDisplayEvent::class => ['loadCustomerFields', 200],
        ];
    }

    public function loadCustomerMeta(CustomerMetaDefinitionEvent $event)
    {
        $entity = $event->getEntity();
        $rules = $this->getRulesForEntityType(MetaFieldsRegistry::CUSTOMER_ENTITY);

        if (empty($rules)) {
            return;
        }

        foreach ($rules as $rule) {
            if (!$this->canSeeRule($rule)) {
                $meta = $entity->getMetaField($rule->getName());
                if (null !== $meta) {
                    $meta->setIsVisible(false);
                }
                continue;
            }

            $entity->setMetaField(
                $this->getMetaDefinitionForRule(new CustomerMeta(), $rule)
            );
        }
    }

    public function loadCustomerFields(CustomerMetaDisplayEvent $event)
    {
        $rules = $this->getRulesForEntityType(MetaFieldsRegistry::CUSTOMER_ENTITY);

        if (empty($rules)) {
            return;
        }

        foreach ($rules as $rule) {
            if (!$rule->isVisible()) {
                continue;
            }

            if (!$this->canSeeRule($rule)) {
                continue;
            }

            $event->addField(
                $this->getMetaDefinitionForRule(new CustomerMeta(), $rule)
            );
        }
    }
}
