<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 27/1/17
 * Time: 1:07 PM
 */

namespace CampusCRM\CampusUserBundle\Migrations\Schema\v0_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;

class ExtendContactEntity implements Migration, ExtendExtensionAwareInterface
{
    protected $extendExtension;

    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_user');
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'gender', // field name
            'gender_source', // enum code
            false, // only one option can be selected
            false, // an administrator can add new options and remove existing ones
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
                'merge' => ['display' => true],
                'dataaudit' => ['auditable' => true]
            ]
        );
        $this->extendExtension->addManyToOneRelation(
            $schema,
            'oro_user', // owning side table
            'contact', // owning side field name
            'orocrm_contact', // inverse side table
            'id', // column name is used to show related entity
            [
                'entity' => ['label' => 'oro.contact.user.label'],
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM]
            ]
        );

        $this->extendExtension->addManyToOneRelation(
            $schema,
            'orocrm_contact', // owning side table
            'user', // owning side field name
            'oro_user', // inverse side table
            'id', // column name is used to show related entity
            [
                'entity' => ['label' => 'oro.contact.user.label'],
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM]
            ]
        );
    }
}
