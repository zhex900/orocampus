<?php

namespace CampusCRM\CampusCalendarBundle\Form\Type;

use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\CalendarBundle\Entity\Attendee;
use CampusCRM\CampusCalendarBundle\Manager\AttendeeRelationManager;
use CampusCRM\CampusCalendarBundle\Manager\AttendeeManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

//use Oro\Bundle\CalendarBundle\Manager\AttendeeManager;

class CalendarEventAttendeesSelectType extends AbstractType
{

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AttendeeManager
     */
    protected $attendeeManager;

    /**
     * @var DataTransformerInterface
     */
    protected $attendeesToViewTransformer;

    /**
     * @var AttendeeRelationManager
     */
    protected $attendeeRelationManager;

    /**
     * @param DataTransformerInterface $attendeesToViewTransformer
     * @param AttendeeManager          $attendeeManager
     * @param AttendeeRelationManager  $attendeeRelationManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        DataTransformerInterface $attendeesToViewTransformer,
        AttendeeManager $attendeeManager,
        AttendeeRelationManager $attendeeRelationManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->attendeesToViewTransformer = $attendeesToViewTransformer;
        $this->attendeeManager            = $attendeeManager;
        $this->attendeeRelationManager    = $attendeeRelationManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->resetViewTransformers();
        $builder->addViewTransformer($this->attendeesToViewTransformer);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['data-selected-data'] = $this->getSelectedData($form);
        if ($form->getData()) {
            $view->vars['configs']['selected'] = $this->attendeeManager->createAttendeeExclusions($form->getData());
        }

        if (!$form->getParent()) {
            return;
        }

        $calendarEvent = $form->getParent()->getData();
        if (!$calendarEvent instanceof CalendarEvent) {
            return;
        }

        if ($calendarEvent->getId() !== null) {
            return;
        }

        // add owner as default attendee when it is not system calendar
        if ($calendarEvent->getSystemCalendar()==null) {
            /** @var User $owner */
            $owner = $this->tokenStorage->getToken()->getUser();

            $this->addOwnerToAttendees($view, $owner);
        }
    }

    /**
     * @param FormView $view
     * @param User    $owner
     */
    private function addOwnerToAttendees(FormView $view, User $owner)
    {
        $view->vars['value'] = json_encode([
            'entityClass' => User::class,
            'entityId'    => $owner->getId(),
        ]);

        $view->vars['attr']['data-selected-data'] = json_encode([
            'text' => $owner->getFullName(),
            'displayName' => $owner->getFullName(),
            'email' => $owner->getEmail(),
            'type' => null,
            'status' => null,
            'userId' => $owner->getId(),
            'id' => $view->vars['value'],
        ]);
    }

    /**
     * @param FormInterface $form
     *
     * @return string
     */
    protected function getSelectedData(FormInterface $form)
    {
        $value = '';
        $attendees = $form->getData();

        if ($attendees) {
            $result = [];
            /**
             * @var Attendee $attendee
             */
            foreach ($attendees as $attendee) {
                $result[] = json_encode(
                    [
                        'text'        => $this->attendeeRelationManager->getDisplayName($attendee),
                        'displayName' => $attendee->getDisplayName(),
                        'email'       => $attendee->getEmail(),
                        'type'        => $attendee->getType() ? $attendee->getType()->getId() : null,
                        'status'      => $attendee->getStatus() ? $attendee->getStatus()->getId() : null,
                        'userId'      => $attendee->getUser() ? $attendee->getUser()->getId() : null,
                        /**
                         * Selected Value Id should additionally encoded because it should be used as string key
                         * to compare with value
                         */
                        'id'          => json_encode(
                            [
                                'entityClass' => Attendee::class,
                                'entityId'    => $attendee->getId(),
                            ]
                        )
                    ]
                );
            }
            $value = implode(';', $result);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'tooltip' => false,
            'layout_template' => false,
            'configs' => function (Options $options, $value) {
                $configs = [
                    'placeholder'        => 'oro.user.form.choose_user',
                    'allowClear'         => true,
                    'multiple'           => true,
                    'separator'          => ';',
                    'forceSelectedData'  => true,
                    'minimumInputLength' => 0,
                    'route_name'         => 'oro_calendarevent_autocomplete_attendees',
                    'component'          => 'attendees',
                    'needsInit'         => $options['layout_template'],
                    'route_parameters'   => [
                        'name' => 'name',
                    ],
                ];

                return $configs;
            }
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'genemu_jqueryselect2_hidden';
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
        return 'campus_calendar_event_attendees_select';
    }
}
