<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\EventSubscriber;

use App\Entity\UserPreference;
use App\Event\UserPreferenceDisplayEvent;
use App\Event\UserPreferenceEvent;
use KimaiPlugin\MetaFieldsBundle\Entity\MetaFieldRule;
use KimaiPlugin\MetaFieldsBundle\MetaFieldsRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class UserPreferenceSubscriber extends AbstractCustomFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            UserPreferenceEvent::class => ['loadUserPreferences', 200],
            UserPreferenceDisplayEvent::class => ['loadUserFields', 200],
        ];
    }

    public function loadUserPreferences(UserPreferenceEvent $event)
    {
        $preferences = $this->getUserPreferences(false);

        foreach ($preferences as $preference) {
            try {
                $event->addPreference($preference);
            } catch (\Exception $ex) {
                $this->logger->error($ex->getMessage());
            }
        }
    }

    public function loadUserFields(UserPreferenceDisplayEvent $event)
    {
        $preferences = $this->getUserPreferences(true);

        foreach ($preferences as $preference) {
            $event->addPreference($preference);
        }
    }

    private function getUserPreferences(bool $onlyVisible = false)
    {
        $rules = $this->getRulesForEntityType(MetaFieldsRegistry::USER_ENTITY);

        $preferences = [];

        foreach ($rules as $rule) {
            if ($onlyVisible && !$rule->isVisible()) {
                continue;
            }

            if (!$this->canSeeRule($rule)) {
                continue;
            }

            $pref = $this->getPreferenceFromRule($rule);

            $preferences[] = $pref;
        }

        return $preferences;
    }

    private function getPreferenceFromRule(MetaFieldRule $rule)
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

        if (!$rule->isRequired()) {
            $options['required'] = false;
        }

        if (!empty($rule->getHelp())) {
            $options['help'] = $rule->getHelp();
        }

        $preference = new UserPreference();
        $preference
            ->setName($rule->getName())
            ->setType($rule->getMappedFieldType())
            ->setValue($value)
            ->setOptions($options)
            ->setOrder($rule->getWeight() + 1000)
            ->setSection($rule->getSection() ?? 'Custom fields')
        ;

        return $preference;
    }
}
