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
class MetaFieldEntityType extends Constraint
{
    public const UNKNOWN_TYPE = 'kimai-meta-field-02';

    /**
     * @var array<string, string>
     */
    protected static $errorNames = [
        self::UNKNOWN_TYPE => 'Unknown entity type.',
    ];

    /**
     * @var string
     */
    public $message = 'Unknown entity type.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
