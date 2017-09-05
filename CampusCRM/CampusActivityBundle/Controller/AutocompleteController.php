<?php

namespace CampusCRM\CampusActivityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Oro\Bundle\FormBundle\Model\AutocompleteRequest;
use CampusCRM\CampusActivityBundle\Autocomplete\ContextSearchHandler;
use Oro\Bundle\ActivityBundle\Controller\AutocompleteController as BaseController;

/**
 * @Route("/activities")
 */
class AutocompleteController extends BaseController
{
    /**
     * @param Request $request
     * @param string $activity The type of the activity entity.
     *
     * @return JsonResponse
     * @throws HttpException|AccessDeniedHttpException
     *
     * @Route("/{activity}/search/autocomplete", name="oro_activity_form_autocomplete_search")
     * AclAncestor("oro_search")
     */
    public function autocompleteAction(Request $request, $activity)
    {
        $autocompleteRequest = new AutocompleteRequest($request);
        $validator           = $this->get('validator');
        $isXmlHttpRequest    = $request->isXmlHttpRequest();
        $code                = 200;
        $result              = [
            'results' => [],
            'hasMore' => false,
            'errors'  => []
        ];

        if ($violations = $validator->validate($autocompleteRequest)) {
            /** @var ConstraintViolation $violation */
            foreach ($violations as $violation) {
                $result['errors'][] = $violation->getMessage();
            }
        }

        if (!$this->get('oro_form.autocomplete.security')->isAutocompleteGranted($autocompleteRequest->getName())) {
            $result['errors'][] = 'Access denied.';
        }

        if (!empty($result['errors'])) {
            if ($isXmlHttpRequest) {
                return new JsonResponse($result, $code);
            }

            throw new HttpException($code, implode(', ', $result['errors']));
        }
        
        /** @var ContextSearchHandler $searchHandler */
        $searchHandler = $this->get('campus_activity.form.handler.autocomplete');
        $searchHandler->setClass($activity);

        return new JsonResponse($searchHandler->search(
            $autocompleteRequest->getQuery(),
            $autocompleteRequest->getPage(),
            $autocompleteRequest->getPerPage(),
            $autocompleteRequest->isSearchById()
        ));
    }
}
