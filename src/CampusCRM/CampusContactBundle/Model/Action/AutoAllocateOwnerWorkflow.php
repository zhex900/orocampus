<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 9/8/17
 * Time: 10:23 PM
 */

namespace CampusCRM\CampusContactBundle\Model\Action;

use Oro\Bundle\ContactBundle\Entity\Contact;
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
        $auto_allocate=$this->contextAccessor->getValue($context, $this->options['auto_allocate']);
        file_put_contents('/tmp/tag.log','workflow action option value: '.$auto_allocate.PHP_EOL,FILE_APPEND);

        $entity=$this->contextAccessor->getValue($context, $this->options['entity_class']);

        if (isset($auto_allocate) && $auto_allocate && $entity instanceof Contact ){
            file_put_contents('/tmp/tag.log','Auto Allocate'.PHP_EOL,FILE_APPEND);
            $this
                ->container
                ->get('oro_contact.auto_owner_allocator')
                ->allocateUser($entity);
        }
        // file_put_contents('/tmp/tag.log','workflow executeAction '.$c.PHP_EOL,FILE_APPEND);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (empty($options['auto_allocate'])) {
            throw new InvalidParameterException('Group name parameter is required');
        }

        if (empty($options['entity_class'])) {
            throw new InvalidParameterException('Entity class parameter is required');
        }

        $this->options=$options;

        return $this;
    }
}
