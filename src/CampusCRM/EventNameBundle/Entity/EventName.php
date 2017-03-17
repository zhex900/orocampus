<?php

namespace CampusCRM\EventNameBundle\Entity;

use CampusCRM\EventNameBundle\Model\ExtendEventName;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\LocaleBundle\Model\NameInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;

/**
 * @ORM\Entity()
 * @ORM\Table(name="orocrm_eventname", indexes={@ORM\Index(name="event_name_idx", columns={"name"})})
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="oro_eventname_index",
 *      routeView="oro_eventname_view",
 *      routeCreate="oro_eventname_create",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-suitcase"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="user_owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="account_management",
 *              "field_acl_supported" = "true"
 *          },
 *          "merge"={
 *              "enable"=true
 *          },
 *          "form"={
 *              "form_type"="oro_eventname_select",
 *              "grid_name"="eventnames-select-grid",
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "grid"={
 *              "default"="eventnames-grid",
 *              "context"="eventnames-for-context-grid"
 *          },
 *          "tag"={
 *              "enabled"=true
 *          }
 *      }
 * )
 */
class EventName extends ExtendEventName implements NameInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=10
     *          }
     *      }
     * )
     */
    protected $id;

    /**
     * Events storage
     *
     * @var ArrayCollection $events
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\CalendarBundle\Entity\CalendarEvent", inversedBy="eventnames")
     * @ORM\JoinTable(name="orocrm_eventname_to_event")
     * @ConfigField(
     *      defaultValues={
     *          "merge"={
     *              "display"=true
     *          },
     *          "importexport"={
     *              "order"=50,
     *              "short"=true
     *          }
     *      }
     * )
     */
    protected $events;


    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @ConfigField(
     *      defaultValues={
     *          "merge"={
     *              "display"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "identity"=true,
     *              "order"=20
     *          }
     *      }
     * )
     */
    protected $name;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "merge"={
     *              "display"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=30,
     *              "short"=true
     *          }
     *      }
     * )
     */
    protected $owner;


    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $updatedAt;


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns the event unique id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  int $id
     * @return EventName
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime
     *
     * @return EventName
     */
    public function setCreatedAt($created)
    {
        $this->createdAt = $created;

        return $this;
    }

    /**
     * Get last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime
     *
     * @return EventName
     */
    public function setUpdatedAt($updated)
    {
        $this->updatedAt = $updated;

        return $this;
    }

    public function __toString()
    {
        return (string)$this->getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set event name
     *
     * @param string $name New name
     *
     * @return EventName
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Pre update event handler
     *
     * @ORM\PreUpdate
     */
    public function doPreUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owningUser
     *
     * @return EventName
     */
    public function setOwner($owningUser)
    {
        $this->owner = $owningUser;

        return $this;
    }

    /**
     * @return EventName
     */
    public function getReferredBy()
    {
        return $this->referredBy;
    }

    /**
     * @param EventName $referredBy
     *
     * @return EventName
     */
    public function setReferredBy(EventName $referredBy = null)
    {
        $this->referredBy = $referredBy;

        return $this;
    }

    /**
     * Add specified event
     *
     * @param CalendarEvent $event
     *
     * @return EventName
     */
    public function addEvent(CalendarEvent $event)
    {
        if (!$this->getEvents()->contains($event)) {
            $this->getEvents()->add($event);
            // $event->addEvent($this);
        }

        return $this;
    }

    /**
     * Get events collection
     *
     * @return Collection|CalendarEvent[]
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Set events collection
     *
     * @param Collection $events
     *
     * @return EventName
     */
    public function setEvents(Collection $events)
    {
        $this->$events = $events;

        return $this;
    }

    /**
     * Remove specified event
     *
     * @param CalendarEvent $event
     *
     * @return EventName
     */
    public function removeEvent(CalendarEvent $event)
    {
        if ($this->getEvents()->contains($event)) {
            $this->getEvents()->removeElement($event);
            //$event->removeAccount($this);
        }

        return $this;
    }

    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     *
     * @return Account
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }
}
