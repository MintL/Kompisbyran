<?php

namespace AppBundle\Controller\Admin2;

use AppBundle\Manager\ConnectionRequestManager;
use AppBundle\Manager\CityManager;
use AppBundle\Entity\ConnectionRequest;
use AppBundle\Entity\User;
use AppBundle\Entity\City;
use AppBundle\Form\EditConnectionRequestType;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @Route("admin2/connectionrequests")
 */
class ConnectionRequestController extends Controller
{
    /**
     * @var ConnectionRequestManager
     */
    private $connectionRequestManager;

    /**
     * @var CityManager
     */
    private $cityManager;

    /**
     * @InjectParams({
     *     "connectionRequestManager"   = @Inject("connection_request_manager"),
     *     "cityManager"                = @Inject("city_manager"),
     *     "formFactory"                = @Inject("form.factory")
     * })
     */
    public function __construct(ConnectionRequestManager $connectionRequestManager, CityManager $cityManager, FormFactoryInterface $formFactory)
    {
        $this->connectionRequestManager = $connectionRequestManager;
        $this->cityManager              = $cityManager;
        $this->formFactory              = $formFactory;
    }

    /**
     * @Route("/ajax-by-city/{id}", name="ajax_by_city", options={"expose"=true})
     * @Method({"GET"})
     */
    public function ajaxByCityAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createAccessDeniedException();
        }

        $city   = $this->cityManager->getFind($request->get('id'));

        $request->getSession()->set('selected_city', $request->get('id'));

        if ($city instanceof City) {
            $results = $this->connectionRequestManager->getFindPaginatedByCityResults($city, $request->get('page', 1));
        } else {
            $results = [
                'success'   => false,
                'message'   => 'City not found!'
            ];
        }

        return new JsonResponse($results);
    }

    /**
     * @Route("/ajax-mark-inspected/{id}", name="admin_ajax_connection_request_mark_inspected", options={"expose"=true})
     * @Method({"GET"})
     */
    public function ajaxMarkInspectedAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createAccessDeniedException();
        }

        return new JsonResponse([
            'success' => $this->connectionRequestManager->markAsInspected($request->get('id'))
        ]);
    }

    /**
     * @Route("/ajax-mark-pending-unpending/{id}", name="admin_ajax_connection_request_mark_pending_or_unpending", options={"expose"=true})
     * @Method({"GET"})
     */
    public function ajaxMarkPendingOrUnpendingAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createAccessDeniedException();
        }

        $connectionRequest = $this->connectionRequestManager->markAsPendingOrUnpending($request->get('id'));

        return new JsonResponse([
            'success'   => ($connectionRequest == false? false: true),
            'label'     => ($connectionRequest->getPending()? 'Remove Pending': 'Make Pending')
        ]);
    }

    /**
     * @Route("/ajax-delete/{id}", name="admin_ajax_connection_request_delete", options={"expose"=true})
     * @Method({"GET"})
     */
    public function ajaxDeleteAction(Request $request, ConnectionRequest $connectionRequest)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createAccessDeniedException();
        }
        $this->connectionRequestManager->remove($connectionRequest);
        return new JsonResponse([
            'success' => true
        ]);
    }

    /**
     * @Route("/ajax-edit/{id}", name="admin_ajax_connection_request_edit", options={"expose"=true})
     * @Method({"POST"})
     */
    public function ajaxEditAction(Request $request, ConnectionRequest $connectionRequest)
    {
        $form = $this->formFactory->create('connectionRequest', $connectionRequest);
        $form->handleRequest($request);
        if ($request->isMethod(Request::METHOD_POST)) {
            if ($form->isValid()) {
                $this->connectionRequestManager->save($connectionRequest);
                return new JsonResponse([
                    'success' => true
                ]);
            }
        }
        return new JsonResponse(['success' => false]);
    }
}
