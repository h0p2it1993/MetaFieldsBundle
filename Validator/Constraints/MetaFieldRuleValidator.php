<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\Validator\Constraints;

use App\Entity\User;
use KimaiPlugin\MetaFieldsBundle\Entity\MetaFieldRule as MetaFieldRuleEntity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MetaFieldRuleValidator extends ConstraintValidator
{
    /**
     * @var TokenStorageInterface
     */
    protected $storage;

    public function __construct(TokenStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param MetaFieldRuleEntity|mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!($constraint instanceof MetaFieldRule)) {
            throw new UnexpectedTypeException($constraint, MetaFieldRule::class);
        }

        if (!\is_object($value) || !($value instanceof MetaFieldRuleEntity)) {
            return;
        }

        if (!$value->isRuleForUser()) {
            return;
        }

        // ignore events like the toolbar where we do not have a token
        if (null === $this->storage->getToken()) {
            return;
        }

        $user = $this->storage->getToken()->getUser();

        if (!($user instanceof User)) {
            return;
        }

        /** @var User $user */
        if (null === $value->getId() && null !== $user->getPreference($value->getName())) {
            $this->context->buildViolation('This name is already in use, please choose another one.')
                ->atPath('name')
                ->setTranslationDomain('validators')
                ->setCode(MetaFieldRule::ALREADY_IN_USE)
                ->addViolation();
        }
    }
}
