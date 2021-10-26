<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\Validator\Constraints;

use KimaiPlugin\MetaFieldsBundle\Entity\MetaFieldRule as MetaFieldRuleEntity;
use KimaiPlugin\MetaFieldsBundle\MetaFieldsRegistry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MetaFieldEntityTypeValidator extends ConstraintValidator
{
    /**
     * @param MetaFieldRuleEntity|mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!($constraint instanceof MetaFieldEntityType)) {
            throw new UnexpectedTypeException($constraint, MetaFieldEntityType::class);
        }

        if ($value === null) {
            return;
        }

        if (!\is_string($value) || !\in_array($value, MetaFieldsRegistry::getAllEntityTypes())) {
            $this->context->buildViolation('Unknown entity type, expected one of: ' . implode(', ', MetaFieldsRegistry::getAllEntityTypes()))
                ->atPath('name')
                ->setTranslationDomain('validators')
                ->setCode(MetaFieldEntityType::UNKNOWN_TYPE)
                ->addViolation();
        }
    }
}
