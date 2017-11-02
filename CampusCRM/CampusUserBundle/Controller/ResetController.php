<?php

namespace CampusCRM\CampusUserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Oro\Bundle\UserBundle\Controller\ResetController as BaseController;

class ResetController extends BaseController
{

    /**
     * @Route("/reset-request", name="oro_user_reset_request")
     * @Method({"GET"})
     * @Template
     */
    public function requestAction()
    {
        $csrfTokenManager = $this->get('security.csrf.token_manager');
        return array('csrf_token'    => $csrfTokenManager->getToken('authenticate')->getValue());
    }
}
