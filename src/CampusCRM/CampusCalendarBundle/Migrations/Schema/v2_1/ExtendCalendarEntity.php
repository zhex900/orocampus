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
        $this->extendExtension->addManyToOneRelation(
            $schema,
            'orocrm_eventname', // owning side table
            'eventname_id', // owning side field name
            'oro_calendar_event', // inverse side table
            'eventname_id', // column name is used to show related entity
            ['extend' => ['owner' => ExtendScope::OWNER_CUSTOM]]
        );
        $this->extendExtension->addManyToOneInverseRelation(
            $schema,
            'orocrm_eventname', // owning side table
            'eventname_id', // owning side field name
            'oro_calendar_event', // inverse side table
            'eventname_id', // inverse side field name
            ['title'], // column names are used to show a title of owning side entity
            ['description'], // column names are used to show detailed info about owning side entity
            ['title'], // Column names are used to show owning side entity in a grid
            ['extend' => ['owner' => ExtendScope::OWNER_CUSTOM]]
        );
    }
}
