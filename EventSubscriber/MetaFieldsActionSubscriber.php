<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\EventSubscriber;

use App\Event\PageActionsEvent;
use App\EventSubscriber\Actions\AbstractActionsSubscriber;
use KimaiPlugin\MetaFieldsBundle\MetaFieldsRegistry;

final class MetaFieldsActionSubscriber extends AbstractActionsSubscriber
{
    public static function getActionName(): string
    {
        return 'meta_fields_bundle';
    }

    public function onActions(PageActionsEvent $event): void
    {
        if (!$event->isIndexView()) {
            $event->addBack($this->path('custom_meta_fields'));
        }

        foreach (MetaFieldsRegistry::getAllEntityTypes() as $entityType) {
            $type = MetaFieldsRegistry::mapEntityTypeToExternalName($entityType);
            $title = MetaFieldsRegistry::mapEntityTypeToTitle($entityType);
            $event->addActionToSubmenu('create', $type, ['url' => $this->path('custom_meta_fields_create', ['entityType' => $type]), 'title' => $title, 'class' => 'modal-ajax-form']);
        }

        $event->addColumnToggle('#modal_meta_fields_admin');

        $event->addHelp('https://www.kimai.org/store/custom-fields-bundle.html');
    }
}
