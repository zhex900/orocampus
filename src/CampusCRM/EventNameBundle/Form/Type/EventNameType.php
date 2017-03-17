<?php

namespace CampusCRM\EventNameBundle\Form\Type;

use Doctrine\Common\Collections\Collection;

use Symfony\Component\Routing\Router;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\ContactBundle\Entity\Contact;
use CampusCRM\EventNameBundle\Entity\EventName;

class EventNameType extends AbstractType
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var EntityNameResolver
     */
    protected $entityNameResolver;

    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var boolean
     */
    private $canViewContact;

    /**
     * @param Router $router
     * @param EntityNameResolver $entityNameResolver
     * @param SecurityFacade $securityFacade
     */
    public function __construct(Router $router, EntityNameResolver $entityNameResolver, SecurityFacade $securityFacade)
    {
        $this->entityNameResolver = $entityNameResolver;
        $this->router = $router;
        $this->securityFacade = $securityFacade;
        $this->canViewContact = $this->securityFacade->isGranted('oro_contact_view');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // name
        $builder->add(
            'name',
            'text',
            array(
                'label' => 'oro.account.name.label',
                'required' => true,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CampusCRM\EventNameBundle\Entity\EventName',
                'intention' => 'eventname',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'cascade_validation' => true
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'event_name';
    }

    /**
     * @param Collection $contacts
     * @param int|null $default
     * @return array
     */
    protected function getInitialElements(Collection $contacts, $default)
    {
        $result = array();
        if ($this->canViewContact) {
            /** @var Contact $contact */
            foreach ($contacts as $contact) {
                if (!$contact->getId()) {
                    continue;
                }
                $primaryPhone = $contact->getPrimaryPhone();
                $primaryEmail = $contact->getPrimaryEmail();
                $result[] = array(
                    'id' => $contact->getId(),
                    'label' => $this->entityNameResolver->getName($contact),
                    'link' => $this->router->generate('oro_contact_info', array('id' => $contact->getId())),
                    'extraData' => array(
                        array('label' => 'Phone', 'value' => $primaryPhone ? $primaryPhone->getPhone() : null),
                        array('label' => 'Email', 'value' => $primaryEmail ? $primaryEmail->getEmail() : null),
                    ),
                    'isDefault' => $default == $contact->getId()
                );
            }
        }
        return $result;
    }
}

