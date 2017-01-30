<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 27/1/17
 * Time: 1:07 PM
 */

namespace CampusCRM\CampusContactBundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;

class ExtendContactEntity implements Migration, ExtendExtensionAwareInterface
{
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_contact');
        $table->addColumn(
            'date_of_baptism',
            'date',
            [
                'oro_options' => [
                    'entity' => [
                        'label' => 'Date of Baptism',
                        'description' => '',
                    ],
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
                    'entity' => [
                        'label' => 'Student ID',
                        'description' => '',
                    ],
                    'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge' => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );

        $this->extendExtension->addManyToOneRelation(
            $schema,
            'orocrm_contact',
            'country_of_birth',
            'oro_dictionary_country',
            'name',
            ['extend' => ['without_default' => true, 'is_extend' => true, 'owner' => ExtendScope::OWNER_CUSTOM]]
        );
    }

    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }


}
