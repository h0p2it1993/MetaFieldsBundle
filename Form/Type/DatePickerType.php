<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\Form\Type;

use App\Form\Type\DatePickerType as CoreDatePickerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Custom form field type to display the date input fields.
 */
class DatePickerType extends CoreDatePickerType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('input', 'string');
    }
}
