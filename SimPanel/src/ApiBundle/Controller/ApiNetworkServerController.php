<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\ApiKeys;
use AppBundle\Entity\NetworkServer;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Service\EncryptionService;

class ApiNetworkServerController extends Controller
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
     * @Rest\Get("/api/v1/network/servers")
     */
    public function networkServerListApi(Request $request)
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
        if (!$auth)
        {
            $data = array(
                // you might translate this message
                'message' => "Unable to validate with the API Key provided",
            );

            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }


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

        $queryBuilder->select('ns')
            ->from('AppBundle:NetworkServer', 'ns')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $result = $queryBuilder->getQuery()->execute();


        foreach ($result as $server)
        {

            $data["server_".$server->getId()]['id'] = $server->getId();
            $data["server_".$server->getId()]['serverName'] = $server->getName();
            $data["server_".$server->getId()]['serverIp'] = $server->getIp();
            $data["server_".$server->getId()]['serverFtpPort'] = $server->getPort();
            $data["server_".$server->getId()]['loginUser'] = $server->getLoginUser();
            $data["server_".$server->getId()]['connectionStatus'] = $server->getConnectionStatus();


        }


        return new JsonResponse($data, 200);

    }

    /**
     * @Rest\Get("/api/v1/network/servers/{id}")
     */
    public function networkServerApiSpecific(Request $request)
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


        // Get Doctrine
        $em = $this->getDoctrine()->getManager();

        $server = $em->getRepository('AppBundle:NetworkServer')->find($request->get('id'));


        if ($server == null) {
            $data = array(
                // you might translate this message
                'message' => "No network server found with the id of {$request->get('id')}",
            );

            return new JsonResponse($data, Response::HTTP_NOT_FOUND);
        }

        $data["server_".$server->getId()]['id'] = $server->getId();
        $data["server_".$server->getId()]['serverName'] = $server->getName();
        $data["server_".$server->getId()]['serverIp'] = $server->getIp();
        $data["server_".$server->getId()]['serverFtpPort'] = $server->getPort();
        $data["server_".$server->getId()]['loginUser'] = $server->getLoginUser();
        $data["server_".$server->getId()]['connectionStatus'] = $server->getConnectionStatus();


        return new JsonResponse($data, 200);

    }

    /**
     * @Rest\Put("/api/v1/network/servers/{id}")
     */
    public function postServerUpdate(Request $request)
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

        $server = $em->getRepository('AppBundle:NetworkServer')->find($request->get('id'));



        if ($server == null) {
            $data = array(
                // you might translate this message
                'message' => "No template found with the id of {$request->get('id')}",
            );

            return new JsonResponse($data, Response::HTTP_NOT_FOUND);
        }

        $server->setIp($request->get('serverIp'));
        $server->setPort($request->get('serverFtpPort'));
        $server->setLoginUser($request->get('loginUser'));
        $server->setName($request->get('serverName'));

        $em->persist($server);

        try{
            $em->flush();
            $message = "Server Updated";
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
     * @Rest\Post("/api/v1/network/servers/create/")
     */
    public function networkServerAPICreate(Request $request)
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

        $server = new NetworkServer();
        $server->setIp($request->get('serverIp'));
        $server->setPort($request->get('serverFtpPort'));
        $server->setLoginUser($request->get('loginUser'));
        $server->setName($request->get('serverName'));
        $server->setSshKey($request->get('sshKey') , $this->getEncryptionService() );
        $server->setConnectionStatus("new");
        $server->setSshPassword($request->get('sshKeyPassword') , $this->getEncryptionService());

        $em->persist($server);

        try {
            $em->flush();
            $message= "Server has been created";
            $status = 201;

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
