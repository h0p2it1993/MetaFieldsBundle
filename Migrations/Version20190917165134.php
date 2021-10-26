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

final class Version20190917165134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add label column';
    }

    public function up(Schema $schema): void
    {
        $metaFields = $schema->getTable('kimai2_meta_field_rules');
        if (!$metaFields->hasColumn('label')) {
            $metaFields->addColumn('label', 'string', ['notnull' => false, 'length' => 50]);
        }
        if (!$metaFields->hasColumn('help')) {
            $metaFields->addColumn('help', 'string', ['notnull' => false, 'length' => 200]);
        }
    }

    public function down(Schema $schema): void
    {
        $metaFields = $schema->getTable('kimai2_meta_field_rules');
        $metaFields->dropColumn('label');
        $metaFields->dropColumn('help');
    }
}
