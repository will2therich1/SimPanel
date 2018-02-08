<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\ApiKeys;
use AppBundle\Entity\ServerTemplate;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Service\NetworkServerService;
use AppBundle\Service\ServerUserService;
use AppBundle\Service\EncryptionService;

class ApiTemplateController extends Controller
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
     * @Rest\Get("/api/v1/templates")
     */
    public function templateApiList(Request $request)
    {
        $data = [];

        // AUTHORISATION START
        if ($request->headers->get('Authorization') == null)
        {
            $headers = apache_request_headers();
            if (isset($headers['Authorization']))
            {
                $sentApiKey = $headers['Authorization'];
            }


        } else {
            $sentApiKey = $request->headers->get('Authorization');
        }

        if(!isset($sentApiKey))
        {
            throw new AccessDeniedException("No Api Key Provided");
        }


        $auth = $this->authorise($sentApiKey);
        if (!$auth) {
            $data = array(
                // you might translate this message
                'message' => "Unable to validate with the API Key provided",
            );

            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }

        // AUTHORISATION END


        $get = $_GET;
        // Set the offset for queries
        if (isset($get['offset']) && $get['offset'] !== '') {
            $offset = $get['offset'];
        } else {
            $offset = 0;
        }

        // Set the limit for queries
        if (isset($get['limit']) && $get['limit'] !== '') {
            $limit = $get['limit'];
        } else {
            $limit = 10;
        }


        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('u')
            ->from('AppBundle:ServerTemplate', 'u')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $result = $queryBuilder->getQuery()->execute();


        foreach ($result as $template )
        {
            $data["template_".$template->getId()] = $template->getTemplateDetails();


        }

        return new JsonResponse($data, 200);

    }

    /**
     * @Rest\Get("/api/v1/templates/{id}")
     */
    public function templateApiSpecific(Request $request)
    {
        $data = [];

        // AUTHORISATION START
        if ($request->headers->get('Authorization') == null)
        {
            $headers = apache_request_headers();
            if (isset($headers['Authorization']))
            {
                $sentApiKey = $headers['Authorization'];
            }


        } else {
            $sentApiKey = $request->headers->get('Authorization');
        }

        if(!isset($sentApiKey))
        {
            throw new AccessDeniedException("No Api Key Provided");
        }


        $auth = $this->authorise($sentApiKey);
        if (!$auth) {
            $data = array(
                // you might translate this message
                'message' => "Unable to validate with the API Key provided",
            );

            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }

        // AUTHORISATION END


        // Get Doctrine
        $em = $this->getDoctrine()->getManager();

        $template = $em->getRepository('AppBundle:ServerTemplate')->find($request->get('id'));


        if ($template == null) {
            $data = array(
                // you might translate this message
                'message' => "No template found with the id of {$request->get('id')}",
            );

            return new JsonResponse($data, Response::HTTP_NOT_FOUND);
        }

        $data[] = $template->getTemplateDetails();


        return new JsonResponse($data, 200);

    }

    /**
     * @Rest\Post("/api/v1/templates/{id}")
     */
    public function postTemplateUpdate(Request $request)
    {
        $data = [];

        // AUTHORISATION START
        if ($request->headers->get('Authorization') == null)
        {
            $headers = apache_request_headers();
            if (isset($headers['Authorization']))
            {
                $sentApiKey = $headers['Authorization'];
            }


        } else {
            $sentApiKey = $request->headers->get('Authorization');
        }

        if(!isset($sentApiKey))
        {
            throw new AccessDeniedException("No Api Key Provided");
        }


        $auth = $this->authorise($sentApiKey);
        if (!$auth) {
            $data = array(
                // you might translate this message
                'message' => "Unable to validate with the API Key provided",
            );

            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }

        // AUTHORISATION END


        $auth = $this->authorise($sentApiKey);
        if (!$auth) {
            $data = array(
                // you might translate this message
                'message' => "Unable to validate with the API Key provided",
            );

            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }




        // Get Doctrine
        $em = $this->getDoctrine()->getManager();

        $template = $em->getRepository('AppBundle:ServerTemplate')->find($request->get('id'));



        if ($template == null) {
            $data = array(
                // you might translate this message
                'message' => "No template found with the id of {$request->get('id')}",
            );

            return new JsonResponse($data, Response::HTTP_NOT_FOUND);
        }

        $template->setTemplateName($request->get('name'));
        $template->setDescription($request->get('description'));

        $em->persist($template);

        try{
            $em->flush();
            $message = "Template Updated";
            $status = 202;
        }catch(\Exception $e)
        {
            $message = "error occoured with message " . $e->getMessage();
            $status = 500;
        }

        $data['message'] = $message;


        return new JsonResponse($data , $status );

    }

    /**
     * @Rest\Post("/api/v1/templates/create/")
     */
    public function templateAPICreate(Request $request)
    {
        $data = [];

        if ($request->headers->get('Authorization') == null)
        {
            $headers = apache_request_headers();
            $sentApiKey = $headers['Authorization'];
        }else {
            $sentApiKey = $request->headers->get('Authorization');
        }

        $auth = $this->authorise($sentApiKey);
        if (!$auth) {
            $data = array(
                // you might translate this message
                'message' => "Unable to validate with the API Key provided",
            );

            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }

        // Get em
        $em = $this->getDoctrine()->getManager();

        $template = new ServerTemplate();
        $template->setConfigId(0);
        $template->setDateCreated(time());
        $template->setLocation('/usr/local/sp/templates');
        $template->setNetworkId($request->get('networkId'));
        $template->setSize('Unknown');
        $template->setStatus('New');
        $template->setSteamName($request->get('steamCmdId'));
        $template->setSteamPercentage(0);
        $template->setTemplateName($request->get('name'));
        $template->setDescription($request->get('templateDescription'));

        $em->persist($template);

        try {
            $em->flush();
            $message= "Template has been created sending to server";
            $status = 201;

            $networkService = new NetworkServerService($this->getEncryptionService() , $em->getRepository('AppBundle:NetworkServer')->find($request->get('networkId')) , $em);

            $httpHost = $request->getHttpHost();
            $templateId = $template->getId();
            $callbackUrl = "https://$httpHost/cron/templateCallback/$templateId";

            $networkService->createTemplate($template->getId() , $template->getSteamName() , $em->getRepository('AppBundle:NetworkServer')->find($template->getNetworkId()) , $callbackUrl);



        }catch (\Exception $e)
        {
            $message = "Failed with the following message:" . $e->getMessage();
            $status = 500;
        }

        $data = array(
            // you might translate this message
            'message' => $message,
        );

        return new JsonResponse($data, $status);

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
        $date = date('Y-m-d H:i:s');

        $apiKey->setLastUsed($date);

        $em->persist($user);
        $em->flush();

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

    /**
     * Makes a instance of the Encryption Service
     *
     * @return EncryptionService
     */
    public function getEncryptionService()
    {
        $encryption_params = $this->container->getParameter('encryption');
        return new EncryptionService($encryption_params);
    }

}
