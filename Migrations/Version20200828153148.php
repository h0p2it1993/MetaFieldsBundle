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

/**
 * @version 1.14
 */
final class Version20200828153148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds group and order columns';
    }

    public function up(Schema $schema): void
    {
        $metaFields = $schema->getTable('kimai2_meta_field_rules');
        $metaFields->addColumn('section', 'string', ['notnull' => false, 'length' => 100]);
        $metaFields->addColumn('weight', 'integer', ['notnull' => true, 'default' => 0]);
    }

    public function down(Schema $schema): void
    {
        $metaFields = $schema->getTable('kimai2_meta_field_rules');
        $metaFields->dropColumn('section');
        $metaFields->dropColumn('weight');
    }
}
