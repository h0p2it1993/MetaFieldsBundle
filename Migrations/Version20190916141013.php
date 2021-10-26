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

final class Version20190916141013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the meta-fields rules table';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('kimai2_meta_field_rules')) {
            return;
        }

        $metaFields = $schema->createTable('kimai2_meta_field_rules');
        $metaFields->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
        $metaFields->addColumn('entity_type', 'string', ['notnull' => true, 'length' => 100]);
        $metaFields->addColumn('name', 'string', ['notnull' => true, 'length' => 50]);
        $metaFields->addColumn('value', 'string', ['notnull' => false, 'length' => 255]);
        $metaFields->addColumn('type', 'string', ['notnull' => true, 'length' => 100]);
        $metaFields->addColumn('customer_id', 'integer', ['notnull' => false]);
        $metaFields->addColumn('project_id', 'integer', ['notnull' => false]);
        $metaFields->addColumn('activity_id', 'integer', ['notnull' => false]);
        $metaFields->addColumn('visible', 'boolean', ['notnull' => true]);
        $metaFields->addColumn('required', 'boolean', ['notnull' => true]);

        $metaFields->setPrimaryKey(['id']);
        $metaFields->addIndex(['entity_type'], 'meta_field_rule_entity_type_idx');
        $metaFields->addUniqueIndex(['entity_type', 'name'], 'meta_field_rule_entity_type_name_uniq');
        $metaFields->addForeignKeyConstraint('kimai2_customers', ['customer_id'], ['id'], ['onDelete' => 'CASCADE']);
        $metaFields->addForeignKeyConstraint('kimai2_projects', ['project_id'], ['id'], ['onDelete' => 'CASCADE']);
        $metaFields->addForeignKeyConstraint('kimai2_activities', ['activity_id'], ['id'], ['onDelete' => 'CASCADE']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('kimai2_meta_field_rules');
    }
}
