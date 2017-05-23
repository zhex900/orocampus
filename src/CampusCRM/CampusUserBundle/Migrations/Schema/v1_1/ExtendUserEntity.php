<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 27/1/17
 * Time: 1:07 PM
 */

namespace CampusCRM\CampusUserBundle\Migrations\Schema\v1_1;

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
        $contact_table = $schema->getTable('orocrm_contact');
        $contact_table->addColumn(
            'user',
            'integer',
            [
                'oro_options' => [
                    'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge' => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );

        $user_table = $schema->getTable('oro_user');
        $user_table->addColumn(
            'contact',
            'integer',
            [
                'oro_options' => [
                    'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge' => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
        $this->extendExtension->addManyToOneRelation(
            $schema,
            'oro_user', // owning side table
            'contact', // owning side field name
            'orocrm_contact', // inverse side table
            'user', // column name is used to show related entity
            [
                'entity' => ['label' => 'oro.contact.user.label'],
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM]
            ]
        );

        $this->extendExtension->addManyToOneInverseRelation(
            $schema,
            'oro_user', // owning side table
            'contact', // owning side field name
            'orocrm_contact', // inverse side table
            'user', // inverse side field name
            ['first_name'], // column names are used to show a title of owning side entity
            ['first_name'], // column names are used to show detailed info about owning side entity
            ['first_name'], // Column names are used to show owning side entity in a grid
            ['extend' => ['owner' => ExtendScope::OWNER_CUSTOM]]
        );
    }
}
