<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MetaFieldExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('form_field_type', [$this, 'getFormFieldTypeName']),
        ];
    }

    public function getFormFieldTypeName(string $classname): string
    {
        $classes = explode('\\', $classname);
        if (!empty($classes)) {
            $classname = end($classes);
        }

        return ucfirst(str_replace('Type', '', $classname));
    }
}
