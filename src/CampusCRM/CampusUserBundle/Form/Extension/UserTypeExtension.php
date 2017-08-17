<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 24/5/17
 * Time: 10:08 AM
 */

namespace CampusCRM\CampusUserBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\ContactBundle\Entity\Contact;

class UserTypeExtension extends AbstractTypeExtension
{

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => ['user_contact_link'],
            ]
        );
    }

    public function getExtendedType()
    {
        return 'oro_user_user';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'id',
                'hidden',
                [
                    'required' => false,
                    'label'    => 'id'
                ]
            );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event) {

                /** @var User $user */
                $user = $event->getData();
                /** @var Contact $contact */
                $contact = $user->getContact();

                if ($contact !== null) {
                    $contact->setUser($user);
                }
                file_put_contents('/tmp/validation.log',
                    'Post submit. -> ', FILE_APPEND);
            }
        );
    }
}