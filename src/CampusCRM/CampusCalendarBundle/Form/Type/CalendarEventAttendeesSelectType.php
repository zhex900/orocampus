<?php

namespace CampusCRM\CampusCalendarBundle\Form\Type;

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

class CalendarEventAttendeesSelectType extends AbstractType
{
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
     */
    public function __construct(
        DataTransformerInterface $attendeesToViewTransformer,
        AttendeeManager $attendeeManager,
        AttendeeRelationManager $attendeeRelationManager
    ) {
        $this->attendeesToViewTransformer = $attendeesToViewTransformer;
        $this->attendeeManager            = $attendeeManager;
        $this->attendeeRelationManager    = $attendeeRelationManager;
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
    }

    /**
     * @param FormInterface $form
     *
     * @return string
     */
    protected function getSelectedData(FormInterface $form)
    {
        file_put_contents('/tmp/attendee.log', 'getSelectedData->>'.PHP_EOL,FILE_APPEND);

        $value = '';
        $attendees = $form->getData();
        file_put_contents('/tmp/attendee.log', 'getSelectedData->> size: '.count($form->getData()).PHP_EOL,FILE_APPEND);

        if ($attendees) {
            $result = [];
            file_put_contents('/tmp/attendee.log', 'Name 2'.PHP_EOL,FILE_APPEND);

            /**
             * @var Attendee $attendee
             */
            foreach ($attendees as $attendee) {
                file_put_contents('/tmp/attendee.log', 'Name'.$attendee->getDisplayName().PHP_EOL,FILE_APPEND);

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
file_put_contents('/tmp/attendee.log', 'result: '. print_r($result,true).PHP_EOL,FILE_APPEND);
            $value = implode(';', $result);
        }
        file_put_contents('/tmp/attendee.log', 'empty'.PHP_EOL,FILE_APPEND);


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
