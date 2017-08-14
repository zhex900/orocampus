<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 9/8/17
 * Time: 10:23 PM
 */

namespace CampusCRM\CampusContactBundle\Model\Action;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Exception\ActionException;
use Oro\Component\ConfigExpression\ContextAccessor;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Component\Action\Exception\InvalidParameterException;

class AutoAllocateOwnerWorkflow extends AbstractAction
{
    /** @var ContainerInterface */
    protected $container;

    /** @var array */
    protected $options;

    /**
     * @param ContextAccessor $contextAccessor
     * @param ContainerInterface $container
     */
    public function __construct(ContextAccessor $contextAccessor,ContainerInterface $container)
    {
        parent::__construct($contextAccessor);
        $this->container=$container;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeAction($context)
    {
        /* @var Contact $entity*/
        $entity=$this->contextAccessor->getValue($context, $this->options['entity_class']);
        /* @var User $owner*/
        $owner=$this->contextAccessor->getValue($context, $this->options['owner']);
        /* @var User $assignto*/
        $assignto=$this->contextAccessor->getValue($context, $this->options['assigned_to']);
        $auto_allocate = $this->contextAccessor->getValue($context, $this->options['auto_allocate']);
        if ($auto_allocate){
            file_put_contents('/tmp/tag.log','Auto Allocate'.PHP_EOL,FILE_APPEND);
            $array = $this
                ->container
                ->get('oro_contact.auto_owner_allocator')
                ->allocateUser($entity);
            $entity->setAutoAllocate(1);
            $owner = $array[0];
            $assignto= $array[1];
            $msg = 'Auto allocation. ';
        }elseif ($owner!= null && $assignto != null) {
            $entity->setOwner($owner);
            $entity->setAssignedTo($assignto);
            $entity->setAutoAllocate(0);
            $msg = 'Manual allocation. ';
        }

        if ($owner!= null && $assignto != null) {
            $this->container->get('session')->getFlashBag()->add('info', $msg .
                'Owner: ' . $owner->getFirstName() . ' ' . $owner->getLastName() .
                '. Assigned to: ' . $assignto->getFirstName() . ' ' . $assignto->getLastName()
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (empty($options['owner'])) {
            throw new InvalidParameterException('Owner parameter is required');
        }
        if (empty($options['assigned_to'])) {
            throw new InvalidParameterException('Assigned_to parameter is required');
        }
        if (empty($options['entity_class'])) {
            throw new InvalidParameterException('Entity class parameter is required');
        }
        if (empty($options['auto_allocate'])) {
            throw new InvalidParameterException('Auto_allocate parameter is required');
        }

        $this->options=$options;

        return $this;
    }
}
