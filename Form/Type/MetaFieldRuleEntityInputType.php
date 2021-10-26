<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\Form\Type;

use KimaiPlugin\MetaFieldsBundle\Entity\MetaFieldRule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MetaFieldRuleEntityInputType extends AbstractType
{
    public const VALID_INPUTS = [
        'text' => 'text',
        'textarea' => 'textarea',
        'integer' => 'integer',
        'email' => 'email',
        'url' => 'url',
        'duration' => 'duration',
        'money' => 'money',
        'number' => 'number',
        'boolean' => 'boolean',
        'datetime' => 'datetime',
        'date' => 'date',
        'invoice-template' => 'invoice-template',
        'choice-list' => MetaFieldRule::CHOICE_TYPE,
        'color' => 'color',
        'language' => 'language',
        'country' => 'country',
        'currency' => 'currency',
        // doesn't work by now:
        // needs refactoring (or a new formtype that converts an array to json or a comma separated list, to store it in the db field) in kimai core
        //'choice-list-multiple' => MetaFieldRule::CHOICE_TYPE_MULTIPLE,
    ];

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $types = self::VALID_INPUTS;

        $resolver->setDefaults([
            'label' => 'entityType',
            'choices' => $types,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
