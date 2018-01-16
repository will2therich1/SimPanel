<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\ApiKeys;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{


    /**
     * The API Key object
     */
    private $keyId = "";

    /**
     * @var string
     */
    private $user = "";

    /**
     * @Rest\Get("/api/v1/welcome")
     */
    public function apiWelcome()
    {
        return new View("Welcome to the API");
    }

    /**
     * @Rest\Get("/api/v1/authtest")
     */
    public function authorisationTest(Request $request)
    {

        $sentApiKey = $request->headers->get('Authorization');
        if ($sentApiKey == null)
        {
            $headers = apache_request_headers();
            $sentApiKey = $headers['Authorization'];
        }

        $auth = $this->authorise($sentApiKey);




        if (!$auth)
        {
            $data = array(
                // you might translate this message
                'message' => "Unable to validate with the API Key provided",
                'Key ID' => $this->keyId,
                'Authorisation Status' => $auth,
                'API Key received' => $sentApiKey,
                'Symfony All Headers recieved' => $request->headers->all(),
                'Apache All Headers recieved' => apache_request_headers(),
            );

            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }else {
            $data = array(
                // you might translate this message
                'message' => "You have been validated",
                'username' => $this->user->getUsername(),
                'Key ID' => $this->keyId,
                'Authorisation Status' => $auth,
                'API Key received' => $sentApiKey,
                'Symfony All Headers recieved' => $request->headers->all(),
                'Apache All Headers recieved' => apache_request_headers(),
            );
            return new JsonResponse($data, 200);

        }
    }

    private function authorise($sendApiKey)
    {
        $this->authoriseApiKey($sendApiKey);

        if ($this->keyId == null)
        {
            return false;

        }

        $apiKey = $this->getKey();

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($apiKey->getOwnerId());

        $this->user = $user;

        if ($user !== null)
        {
            return true;
        }

    }

    /**
     * Authorises the API key sent on request
     *
     * @param $apiKey
     */
    private function authoriseApiKey($apiKey)
    {
        $em = $this->getDoctrine()->getManager();

        $keys = $em->getRepository('ApiBundle:ApiKeys')->findAll();

        print_r($keys);

        foreach ($keys as $key)
        {


            $verify = password_verify($apiKey ,$key->getApiKey());


            if ($verify == true)
            {

                $this->keyId = $key->getId();

                break;

            }

        }


    }

    /**
     * Return the API Key Object
     */
    private function getKey()
    {
        $em = $this->getDoctrine()->getManager();

        $key = $em->getRepository('ApiBundle:ApiKeys')->find($this->keyId);

        return $key;

    }




}
