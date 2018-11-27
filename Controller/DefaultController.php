<?php

namespace K2klettern\ApiTokenConexBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/test-token")
     */
    public function indexAction()
    {
        $helper = $this->get('api_token_conex.apitokenhelper');
        $data = $helper->getData('https://kennzeichen-admin.nettraders.biz/ajax/kennzeichen/kuerzels/BO');
        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
