<?php

namespace CampusCRM\EventNameBundle\Migrations\Schema\v2_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\SecurityBundle\Migrations\Schema\UpdateOwnershipTypeQuery;

class EventNameBundle implements Migration
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        // @codingStandardsIgnoreStart

        /** Generate table eventname **/
        $table = $schema->createTable('orocrm_eventname');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', []);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_owner_id'], 'IDX_7166D3719EB185F9', []);
        $table->addIndex(['name'], 'eventname_name_idx', []);

        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addIndex(['organization_id'], 'IDX_7166D37132C8A3DE', []);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );

        //Add organization fields to ownership entity config
        $queries->addQuery(
            new UpdateOwnershipTypeQuery(
                'CampusCRM\EventNameBundle\Entity\EventName',
                [
                    'organization_field_name' => 'organization',
                    'organization_column_name' => 'organization_id'
                ]
            )
        );

        /** End of generate table eventname **/

        /** Generate table orocrm_eventname_to_event **/
        $table = $schema->createTable('orocrm_eventname_to_event');
        $table->addColumn('eventname_id', 'integer', []);
        $table->addColumn('event_id', 'integer', []);
        $table->setPrimaryKey(['eventname_id']);
        $table->addIndex(['event_id']);//, 'IDX_65B8FBEC9B6B5FBZ', []);
        /** End of generate table oro_account_to_contact **/


        /** Generate foreign keys for table orocrm_eventname_to_event **/
        $table = $schema->getTable('orocrm_eventname_to_event');
        $table->addForeignKeyConstraint($schema->getTable('oro_calendar_event'), ['event_id'], ['id'], ['onDelete' => 'CASCADE', 'onUpdate' => null]);
        $table->addForeignKeyConstraint($schema->getTable('orocrm_eventname'), ['eventname_id'], ['id'], ['onDelete' => 'CASCADE', 'onUpdate' => null]);
        /** End of generate foreign keys for table orocrm_eventname_to_event **/
        // @codingStandardsIgnoreEnd
    }
}
