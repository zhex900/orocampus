<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 27/1/17
 * Time: 1:07 PM
 */

namespace CampusCRM\CampusCalendarBundle\Migrations\Schema\v2_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;

class ExtendCalendarEntity implements Migration, ExtendExtensionAwareInterface
{
    protected $extendExtension;

    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_calendar_event');
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'event_name', // new field name
            'event_name_source', // enum code
            false, // only one option can be selected
            true, // an administrator can add new options and remove existing ones
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                'merge' => ['display' => true],
                'dataaudit' => ['auditable' => true]
            ]
        );

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'event_category', // new field name
            'event_category_source', // enum code
            false, // only one option can be selected
            true, // an administrator can add new options and remove existing ones
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                'merge' => ['display' => true],
                'dataaudit' => ['auditable' => true]
            ]
        );
    }
}
