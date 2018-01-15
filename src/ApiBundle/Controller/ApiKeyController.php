<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyController extends Controller
{

    /**
     * @Route("/api/keys/delete/{id}" , name="deleteApiKeyID")
     */
    public function deleteApiKey(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $apiKey = $em->getRepository('ApiBundle:ApiKeys')->find($request->get('id'));
        $em->remove($apiKey);
        $em->flush();


        $data = array(
            // you might translate this message
            'message' => 'Api Key removed'
        );

        return new JsonResponse($data, Response::HTTP_ACCEPTED);

    }

}
