<?php

namespace CampusCRM\EventNameBundle\Migrations\Schema\v0_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;

class ExtendEventNameEntity implements Migration, ExtendExtensionAwareInterface
{
    protected $extendExtension;

    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    public function up(Schema $schema, QueryBag $queries)
    {
        // @codingStandardsIgnoreStart
        $this->extendExtension->addManyToOneRelation(
            $schema,
            'oro_calendar_event', // owning side table
            'oro_eventname', // owning side field name
            'orocrm_eventname', // inverse side table
            'name', // column name is used to show related entity
            [
                'entity' => ['label' => 'oro.calendar.calendarevent.oro_eventname.label'],
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM]
            ]
        );

        $this->extendExtension->addManyToOneInverseRelation(
            $schema,
            'oro_calendar_event', // owning side table
            'oro_eventname', // owning side field name
            'orocrm_eventname', // inverse side table
            'events', // inverse side field name
            ['title'], // column names are used to show a title of owning side entity
            ['title'], // column names are used to show detailed info about owning side entity
            ['title'], // Column names are used to show owning side entity in a grid
            ['extend' => ['owner' => ExtendScope::OWNER_CUSTOM]]
        );
        /** End of generate table eventname **/
    }
}
