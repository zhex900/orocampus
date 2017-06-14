<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 27/1/17
 * Time: 1:07 PM
 */

namespace CampusCRM\CampusContactBundle\Migrations\Schema\v0_1;

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
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {

        $this->extendExtension->addManyToOneRelation(
            $schema,
            'orocrm_contact',
            'country_of_birth',
            'oro_dictionary_country',
            'name',
            ['extend' => ['without_default' => true, 'is_extend' => true, 'owner' => ExtendScope::OWNER_CUSTOM]]
        );

        $table = $schema->getTable('orocrm_contact');

        $table->addColumn(
            'date_of_baptism',
            'date',
            [
                'oro_options' => [
                    'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge' => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
        $table->addColumn(
            'student_id',
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

        $table->addColumn(
            'year_of_birth',
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

        $table->addColumn(
            'int_student',
            'boolean',
            [
                'oro_options' => [
                    'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge' => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
        $table->addColumn(
            'out_of_town',
            'boolean',
            [
                'oro_options' => [
                    'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge' => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );

        $table->addColumn(
            'christian',
            'boolean',
            [
                'oro_options' => [
                    'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge' => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );

        $table->addColumn(
            'believed_thr_us',
            'boolean',
            [
                'oro_options' => [
                    'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge' => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );

        $table->addColumn(
            'baptised_by_us',
            'boolean',
            [
                'oro_options' => [
                    'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge' => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );

        $table->addColumn(
            'first_contact_date',
            'date',
            [
                'oro_options' => [
                    'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge' => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );

        $table->addColumn(
            'date_believed',
            'date',
            [
                'oro_options' => [
                    'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge' => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );

        $table->addColumn(
            'duration',
            'float',
            [
                'oro_options' => [
                    'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge' => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
        $table->addColumn(
            'year_of_commencement',
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
        $table->addColumn(
            'semester_contacted',
            'string',
            [
                'oro_options' => [
                    'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge' => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
    }
}
