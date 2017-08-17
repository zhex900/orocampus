<?php

namespace CampusCRM\EventNameBundle\Migrations\Schema\v0_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;

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
        $table = $schema->getTable('orocrm_eventname');

        $table->addColumn('system_calendar', 'boolean',
            [
                'oro_options' => [
                    'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
                    'merge' => ['display' => false],
                    'dataaudit' => ['auditable' => false]
                ]
            ]
            );

        /** End of generate table eventname **/
    }
}
