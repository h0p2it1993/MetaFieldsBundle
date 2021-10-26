<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\EventSubscriber;

use App\Event\PermissionSectionsEvent;
use App\Model\PermissionSection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PermissionSectionSubscriber implements EventSubscriberInterface
{
    public const SECTION_TITLE = 'Custom fields (plugin)';

    public static function getSubscribedEvents(): array
    {
        return [
            PermissionSectionsEvent::class => ['onEvent', 100],
        ];
    }

    public function onEvent(PermissionSectionsEvent $event)
    {
        $event->addSection(new PermissionSection(self::SECTION_TITLE, '_meta_fields'));
    }
}
