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
