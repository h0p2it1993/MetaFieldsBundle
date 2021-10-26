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

final class Version20190916163556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change auto-generated index name';
    }

    public function up(Schema $schema): void
    {
        $metaFields = $schema->getTable('kimai2_meta_field_rules');
        // in earlier versions the name was not hardcoded, leave this migration for early adopters
        if ($metaFields->hasIndex('UNIQ_C7D8A261C412EE025E237E06')) {
            $metaFields->renameIndex('UNIQ_C7D8A261C412EE025E237E06', 'meta_field_rule_entity_type_name_uniq');
        }
    }

    public function down(Schema $schema): void
    {
        $metaFields = $schema->getTable('kimai2_meta_field_rules');
        $metaFields->renameIndex('meta_field_rule_entity_type_name_uniq', 'UNIQ_C7D8A261C412EE025E237E06');
    }
}
