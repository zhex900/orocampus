<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 27/1/17
 * Time: 1:07 PM
 */

namespace CampusCRM\CampusCallBundle\Migrations\Schema\v0_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;

class ExtendCallEntity implements Migration, ExtendExtensionAwareInterface
{
    protected $extendExtension;

    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_call');
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'call_type', // field name
            'call_type_source', // enum code
            false, // only one option can be selected
            true, // an administrator can add new options and remove existing ones
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
                'merge' => ['display' => false],
                'dataaudit' => ['auditable' => false]
            ]
        );

        // @codingStandardsIgnoreStart
        $this->extendExtension->addManyToOneRelation(
            $schema,
            'orocrm_call', // owning side table
            'related_contact', // owning side field name
            'orocrm_contact', // inverse side table
            'id', // column name is used to show related entity
            [
                'entity' => ['label' => 'campus.call.contact.label'],
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM]
            ]
        );
    }
}

