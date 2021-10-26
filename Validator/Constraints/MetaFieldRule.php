<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\Validator\Constraints;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD", "ANNOTATION"})
 */
class MetaFieldRule extends Constraint
{
    public const ALREADY_IN_USE = 'kimai-meta-field-01';

    /**
     * @var array<string, string>
     */
    protected static $errorNames = [
        self::ALREADY_IN_USE => 'This name is already in use, please choose another one.',
    ];

    /**
     * @var string
     */
    public $message = 'This custom field has invalid settings.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
