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
        $owner_view = new View('oro_contact.owner',
            ['owner'            => ['value' => $owner->getId(), 'type' => '1']],
            ['review_request'   => ['value' => 0]]
        );
        $owner_view->setLabel($this->translator->trans('oro.contact.gridview.review_request.label'));

        return [$owner_view]; //array_merge([$owner_view],parent::getViewsList());
    }
}