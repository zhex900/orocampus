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
    public function __construct(ContextAccessor $contextAccessor, ContainerInterface $container)
    {
        parent::__construct($contextAccessor);
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeAction($context)
    {
        /* @var Contact $entity*/
        $entity = $this->contextAccessor->getValue($context, $this->options['entity_class']);
        /* @var User $owner*/
        $owner = $this->contextAccessor->getValue($context, $this->options['owner']);
        $auto_allocate = $this->contextAccessor->getValue($context, $this->options['auto_allocate']);
        if ($auto_allocate) {
            file_put_contents('/tmp/tag.log', 'Auto Allocate' . PHP_EOL, FILE_APPEND);
            $owner = $this
                ->container
                ->get('oro_contact.auto_owner_allocator')
                ->allocateUser($entity);
            $entity->setAutoAllocate(1);
            $msg = 'Auto allocation. ';
        } elseif ($owner != null) {
            $entity->setOwner($owner);
            $entity->setAutoAllocate(0);
            $msg = 'Manual allocation. ';
        }

        if ($owner != null) {
            $this->container->get('session')->getFlashBag()->add('info', $msg .
                'Owner: ' . $owner->getFirstName() . ' ' . $owner->getLastName());
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
        if (empty($options['entity_class'])) {
            throw new InvalidParameterException('Entity class parameter is required');
        }
        if (empty($options['auto_allocate'])) {
            throw new InvalidParameterException('Auto_allocate parameter is required');
        }

        $this->options = $options;

        return $this;
    }
}
