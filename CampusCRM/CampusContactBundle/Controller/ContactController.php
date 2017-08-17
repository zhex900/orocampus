<?php

namespace CampusCRM\CampusContactBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Controller\ContactController as BaseController;

class ContactController extends BaseController
{
    /**
     * @Route("/info/{id}block{block}", name="oro_contact_info", requirements={"id"="\d+","block"="\w+"})
     *
     * @Template
     * @AclAncestor("oro_contact_view")
     */
    public function infoAction(Contact $contact)
    {

        $request = $this->getRequest()->getRequestUri();
        preg_match('/block(.*)\?/', $request, $matches);
        $block = $matches[1];

        if (!$this->getRequest()->get('_wid')) {
            return $this->redirect($this->get('router')->generate('oro_contact_view', ['id' => $contact->getId(), 'block'=>$block]));
        }

        // file_put_contents('/tmp/url.log','$request: '. $request.PHP_EOL,FILE_APPEND);
        return array(
            'entity'  => $contact,
            'block' => $block
        );
    }
}
