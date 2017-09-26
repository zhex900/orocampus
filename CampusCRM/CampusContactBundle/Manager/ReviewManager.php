<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 24/9/17
 * Time: 9:30 PM
 */

namespace CampusCRM\CampusContactBundle\Manager;


use Oro\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ReviewManager
{
    // key : step name
    // value: number of days until the next review
    const REVIEW_LIMIT = ['unassigned' => 1, 'assigned' => 14, 'contacted' => 14, 'followup'=>28];

    /* @var \DateTIme */
    protected $today;

    /* @var ContainerInterface $container */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->today = new \DateTime();
        $this->today->setTimezone(new \DateTimeZone('UTC'));
    }

    public function applyReviewRulesForContactFollowUp(){
        $followup = $this->container->get('campus_contact.workflow.manager')::CONTACT_FOLLOWUP;
        $this->applyReviewRule($followup,'unassigned');
        $this->applyReviewRule($followup,'assigned');
        $this->applyReviewRule($followup,'contacted');
        $this->applyReviewRule($followup,'followup');
    }
    /*
    * Helper function to apply review to all contacts at
    * given step.
    *
    * @param string $at step name
    */
    public function applyReviewRule($workflow, $at)
    {
        if (!array_key_exists(strtolower($at), self::REVIEW_LIMIT)){
            return;
        }
        // Get a list of all the contacts that are at step $from
        /** @var Contact[] $contacts */
        $contacts = $this->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('OroContactBundle:Contact')
            ->findByWorkflowStep($workflow, $at);

        $this->container->get('logger')
            ->debug('ReviewManager. Apply review rule ' . 'for workflow: ' . $workflow . ' at '
            . $at .'. contacts# ' . sizeof($contacts));

        foreach ($contacts as $contact) {
            $review = 'NO';
            // how many days since the last review?
            if (\date_diff($this->getLastReviewDate($contact),$this->today)->days >= self::REVIEW_LIMIT[$at]) {
                if (!$contact->getReviewRequest()) {
                    $contact->setReviewRequest(true);
                    $this->container->get('doctrine.orm.entity_manager')->flush($contact);
                }
                // add notification
                $review = 'YES';
            }
            $this->container->get('logger')->debug('ReviewManager. Review ' . $at . ': '.$review.' for contact ' . $contact->getFirstName() . ' ' . $contact->getLastName());
        }
    }

    protected function getLastReviewDate(Contact $contact){
        return ($contact->getLastReview()==null? $contact->getFirstContactDate():$contact->getLastReview());
    }

}