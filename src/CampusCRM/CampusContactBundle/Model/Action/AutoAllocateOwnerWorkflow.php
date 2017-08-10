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

        if ($owner == null || $assignto == null ){
            file_put_contents('/tmp/tag.log','Auto Allocate'.PHP_EOL,FILE_APPEND);
            $array = $this
                ->container
                ->get('oro_contact.auto_owner_allocator')
                ->allocateUser($entity);
            $entity->setAutoAllocate(1);
            $owner = $array[0];
            $assignto= $array[1];
            $msg = 'Auto allocation. ';
        }else{
            $entity->setOwner($owner);
            $entity->setAssignedTo($assignto);
            $entity->setAutoAllocate(0);
            $msg = 'Manual allocation. ';
        }

        $this->container->get('session')->getFlashBag()->add('info', $msg.
            'Owner: '.$owner->getFirstName(). ' '.$owner->getLastName().
            '. Assigned to: '.$assignto->getFirstName(). ' '.$assignto->getLastName()
            );
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (empty($options['owner'])) {
            throw new InvalidParameterException('Group name parameter is required');
        }
        if (empty($options['assigned_to'])) {
            throw new InvalidParameterException('Group name parameter is required');
        }
        if (empty($options['entity_class'])) {
            throw new InvalidParameterException('Entity class parameter is required');
        }

        $this->options=$options;

        return $this;
    }
}
