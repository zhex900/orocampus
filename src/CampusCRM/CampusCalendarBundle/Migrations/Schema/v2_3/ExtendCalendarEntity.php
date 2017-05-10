<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 27/1/17
 * Time: 1:07 PM
 */

namespace CampusCRM\CampusCalendarBundle\Migrations\Schema\v2_3;

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
            'oro_calendar_event_attendee', // owning side table
            'contact', // owning side field name
            'orocrm_contact', // inverse side table
            'id', // column name is used to show related entity
            [
                'entity' => ['label' => 'oro.calendar.calendarevent.attendee.contact.label'],
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM]
            ]
        );
    }
}
