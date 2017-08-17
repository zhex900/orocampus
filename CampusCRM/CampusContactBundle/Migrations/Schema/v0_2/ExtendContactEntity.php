<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 27/1/17
 * Time: 1:07 PM
 */

namespace CampusCRM\CampusContactBundle\Migrations\Schema\v0_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;

class ExtendContactEntity implements Migration, ExtendExtensionAwareInterface
{
    protected $extendExtension;

    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_contact');
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'ethnicity', // new field name
            'ethnicity_source', // enum code
            false, // only one option can be selected
            true, // an administrator can add new options and remove existing ones
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
                'merge' => ['display' => true],
                'dataaudit' => ['auditable' => true]
            ]
        );

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'marital_status', // new field name
            'marital_status_source', // enum code
            false, // only one option can be selected
            true, // an administrator can add new options and remove existing ones
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
                'merge' => ['display' => true],
                'dataaudit' => ['auditable' => true]
            ]
        );

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'contact_source', // new field name
            'contact_source_source', // enum code
            false, // only one option can be selected
            true, // an administrator can add new options and remove existing ones
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
                'merge' => ['display' => true],
                'dataaudit' => ['auditable' => true]
            ]
        );

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'contact_status', // new field name
            'contact_status_source', // enum code
            false, // only one option can be selected
            true, // an administrator can add new options and remove existing ones
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
                'merge' => ['display' => true],
                'dataaudit' => ['auditable' => true]
            ]
        );

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'degrees', // new field name
            'degrees_source', // enum code
            false, // only one option can be selected
            true, // an administrator can add new options and remove existing ones
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
                'merge' => ['display' => true],
                'dataaudit' => ['auditable' => true]
            ]
        );

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'level_of_study', // new field name
            'level_of_study_source', // enum code
            false, // only one option can be selected
            true, // an administrator can add new options and remove existing ones
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
                'merge' => ['display' => true],
                'dataaudit' => ['auditable' => true]
            ]
        );

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'month_of_commencement', // new field name
            'month_of_comm_source', // enum code
            false, // only one option can be selected
            true, // an administrator can add new options and remove existing ones
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
                'merge' => ['display' => true],
                'dataaudit' => ['auditable' => true]
            ]
        );

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'institutions', // new field name
            'institutions_source', // enum code
            false, // only one option can be selected
            true, // an administrator can add new options and remove existing ones
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
                'merge' => ['display' => true],
                'dataaudit' => ['auditable' => true]
            ]
        );
    }
}
