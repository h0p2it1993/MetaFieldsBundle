<?php

declare(strict_types=1);

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MetaFieldsBundle\Migrations;

use App\Doctrine\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @version 1.16
 */
final class Version20210223154013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allows arbitrary length for the default value column';
    }

    public function up(Schema $schema): void
    {
        $metaFields = $schema->getTable('kimai2_meta_field_rules');
        $column = $metaFields->getColumn('value');
        $column->setOptions(['length' => null]);
        $column->setType(Type::getType(Types::TEXT));
    }

    public function down(Schema $schema): void
    {
        $metaFields = $schema->getTable('kimai2_meta_field_rules');
        $column = $metaFields->getColumn('value');
        $column->setType(Type::getType(Types::STRING));
        $column->setOptions(['length' => 255]);
    }
}
