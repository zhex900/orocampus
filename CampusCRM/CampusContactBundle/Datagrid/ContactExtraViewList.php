<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 2/8/17
 * Time: 11:54 AM
 */

namespace CampusCRM\CampusContactBundle\Datagrid;

use Oro\Bundle\ContactBundle\Datagrid\ContactsViewList;
use Oro\Bundle\DataGridBundle\Extension\GridViews\View;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ContactExtraViewList extends ContactsViewList
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function setToken(TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    protected function getViewsList()
    {
        /** @var User $owner */
        $owner = $this->tokenStorage->getToken()->getUser();
        $owner_view = new View('oro_contact.owner', ['owner' => ['value' => $owner->getId(), 'type' => '1']]);
        $owner_view->setLabel($this->translator->trans('oro.contact.gridview.owner.label'));

        $followup_view = new View('oro_contact.followup', ['owner' => ['value' => $owner->getId(), 'type' => '1']]);
        $followup_view->setLabel($this->translator->trans('oro.contact.gridview.followup.label'));
        $columns = $followup_view->getColumnsData();
        file_put_contents('/tmp/a.log', print_r($columns, true), FILE_APPEND);
        $followup_view->setColumnsData(['first_name' => ['renderable' => true, 'order' => 1],
                                        'mhl' => ['renderable' => 0, 'order' => 2],
                                        'birthday' => ['renderable' => 0, 'order' => 3],
                                        'fax' => ['renderable' => 0, 'order' => 4],
                                        'gender' => ['renderable' => 0, 'order' => 4],
                                        'skype' => ['renderable' => 0, 'order' => 4],
            'addressCity' => ['renderable' => 0, 'order' => 4],
            'addressPostalCode' => ['renderable' => 0, 'order' => 4],
            'addressStreet' => ['renderable' => 0, 'order' => 4],
            'baptised_by_us' => ['renderable' => 0, 'order' => 4],
            'believed_thr_us' => ['renderable' => 0, 'order' => 4],
            'contact_status' => ['renderable' => 0, 'order' => 4],
            'countryName' => ['renderable' => 0, 'order' => 4],
            'createdAt' => ['renderable' => 0, 'order' => 4],
            'degrees' => ['renderable' => 0, 'order' => 4],
            'ethnicity' => ['renderable' => 0, 'order' => 4],
            'facebook' => ['renderable' => 0, 'order' => 4],
            'googlePlus' => ['renderable' => 0, 'order' => 4],
            'marital_status' => ['renderable' => 0, 'order' => 4],
            'month_of_commencement' => ['renderable' => 0, 'order' => 4],
            'twitter' => ['renderable' => 0, 'order' => 4],
            'year_of_birth' => ['renderable' => 0, 'order' => 4],
            'user' => ['renderable' => 0, 'order' => 4],
            'source' => ['renderable' => 0, 'order' => 4],
            'reportsName' => ['renderable' => 0, 'order' => 4],
            'regionLabel' => ['renderable' => 0, 'order' => 4],
            'out_of_town' => ['renderable' => 0, 'order' => 4],
            'institutions' => ['renderable' => 0, 'order' => 4],
            'linkedIn' => ['renderable' => 0, 'order' => 4],
            'updatedAt' => ['renderable' => 0, 'order' => 4],
            'pictureFilename' => ['renderable' => 0, 'order' => 4],
            'timesContacted' => ['renderable' => 0, 'order' => 4],
            'timesContactedIn' => ['renderable' => 0, 'order' => 4],
            'timesContactedOut' => ['renderable' => 0, 'order' => 4],
            'lastContactedDate' => ['renderable' => 0, 'order' => 4],
            'lastContactedDateIn' => ['renderable' => 0, 'order' => 4],
            'lastContactedDateOut' => ['renderable' => 0, 'order' => 4],
            'daysSinceLastContact' => ['renderable' => 0, 'order' => 4],
            'email' => ['renderable' => 0, 'order' => 4],
            'level_of_study' => ['renderable' => 0, 'order' => 4],
            'country_of_birth' => ['renderable' => 0, 'order' => 4]
                                        ]);
        $columns = $followup_view->getColumnsData();
        file_put_contents('/tmp/a.log', print_r($columns, true), FILE_APPEND);

        return array_merge(parent::getViewsList(), [$owner_view, $followup_view]);
    }
}