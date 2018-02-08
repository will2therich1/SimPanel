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
use ServerBundle\Entity\GameServer;
use AppBundle\Service\EncryptionService;
use AppBundle\Service\NetworkServerService;
use AppBundle\Service\ServerUserService;


class ApiGameServerController extends Controller
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
     * @Rest\Get("/api/v1/game/servers")
     */
    public function gameServerListApi(Request $request)
    {
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

        $queryBuilder->select('gs')
            ->from('ServerBundle:GameServer', 'gs')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $result = $queryBuilder->getQuery()->execute();

        $data = [];

        foreach ($result as $server)
        {

            $data["server_".$server->getId()] = $server->getServerInfomation();


        }


        return new JsonResponse($data, 200);

    }

    /**
     * @Rest\Get("/api/v1/game/servers/{id}")
     */
    public function gameServerApiSpecific(Request $request)
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

        $server = $em->getRepository('ServerBundle:GameServer')->find($request->get('id'));


        if ($server == null) {
            $data = array(
                // you might translate this message
                'message' => "No game server found with the id of {$request->get('id')}",
            );

            return new JsonResponse($data, Response::HTTP_NOT_FOUND);
        }

        $data["server_".$server->getId()] = $server->getServerInfomation();


        return new JsonResponse($data, 200);

    }

    /**
     * @Rest\Post("/api/v1/game/servers/{id}")
     */
    public function postGameServerUpdate(Request $request)
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

        $server = $em->getRepository('ServerBundle:GameServer')->find($request->get('id'));


        if ($server == null) {
            $data = array(
                // you might translate this message
                'message' => "No game server found with the id of {$request->get('id')}",
            );

            return new JsonResponse($data, Response::HTTP_NOT_FOUND);
        }

        $server->setServerName($request->get('serverName'));
        $server->setPlayerSlots($request->get('playerSlots'));
        $server->setRam($request->get('ram'));
        $server->setStartupCommand($request->get('startCMD'));
        $server->setUpdateCommand($request->get('updateCMD'));



        $em = $this->getDoctrine()->getManager();
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
     * @Rest\Post("/api/v1/game/servers/create/defaults/{defaultId}/{ownerId}")
     */
    public function serverCreateDefaults(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();

        // Get the params from the url.
        $configId = $request->get('defaultId');
        $ownerId = $request->get('ownerId');

        // Get our objects from the database
        $config = $em->getRepository('ServerBundle:defaultConfiguration')->find($configId);
        $user = $em->getRepository('AppBundle:User')->find($ownerId);
        $template = $em->getRepository('AppBundle:ServerTemplate')->find($config->getTemplateId());
        $networkServer = $em->getRepository('AppBundle:NetworkServer')->find($template->getNetworkId());

        if ($user->getSubUser() == 1)
        {

            $data = array(
                // you might translate this message
                'message' => "Cannot give a sub user a server",
            );
            return new JsonResponse($data, Response::HTTP_METHOD_NOT_ALLOWED);
        }

        // Get the server IP and generate a port.
        $serverIp = $networkServer->getIp();
        $port =  $this->findOpenPort($serverIp);

        // Create the server database Object
        $server = new GameServer();
        $server->setGameName($config->getGameName());
        $server->setServerName($this->serverNameReplacement($config->getDefaultServerName() , $user , $template));
        $server->setOwnerId($ownerId);
        $server->setPlayerSlots($config->getDefaultPlayerSlots());
        $server->setRam($config->getDefaultRam());
        $server->setStartupCommand($config->getStartupCommand());
        $server->setUpdateCommand($config->getUpdateCommand());
        $server->setTemplateId($config->getTemplateId());
        $server->setQueryEngine('Not Implemented');
        $server->setIp($serverIp);
        $server->setPort($port);
        $server->setLocation('/');
        $server->setStatus("New");

        // Persist the server and flush to update the DB.
        $em->persist($server);
        $em->flush();


        $username = $user->getUsername();

        // Update the server location
        $server->setLocation("/usr/local/sp/users/$username/$serverIp.$port");
        $em->persist($server);


        try{
            $em->flush();
            $message = "Server Created";
            $status = 201;
        }catch(\Exception $e)
        {
            $message = "error occoured with message " . $e->getMessage();
            $status = 500;
            return new JsonResponse($data, $status);
        }

        // Lets work on the actual server now!
        $enc_service = $this->getEncryptionService();

        // Create the needed services
        $networkServerService = new NetworkServerService($enc_service ,$networkServer , $em);
        $serverUserService = new ServerUserService($enc_service , $networkServer , $user , $em);

        // See if the current user already has a server account.
        if ($user->getServerUser() === 0)
        {
            $serverUserService->createUser($user->getUsername());
        }

        // Get the server Id
        $serverId = $networkServer->getId();
        $callbackUrl = $request->getHttpHost() . "/cron/serverCallback/$serverId";

        // Actually create the server.
        $networkServerService->serverCreation($user , $networkServer , $template , $callbackUrl , $port);

        $data = array(
            // you might translate this message
            'message' => $message,
        );
        return new JsonResponse($data, $status);

    }

    /**
     * @Rest\Post("/api/v1/game/servers/create/{templateId}/{ownerId}")
     */
    public function serverCreateTemplates(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get Settings Service

        // Get the params from the url.
        $templateId = $request->get('templateId');
        $ownerId = $request->get('ownerId');

        // Get our objects from the database
        $user = $em->getRepository('AppBundle:User')->find($ownerId);
        $template = $em->getRepository('AppBundle:ServerTemplate')->find($templateId);
        $networkServer = $em->getRepository('AppBundle:NetworkServer')->find($template->getNetworkId());

        // Get the server IP and generate a port.
        $serverIp = $networkServer->getIp();
        $port =  $this->findOpenPort($serverIp);

        if ($user->getSubUser() == 1)
        {
            $data = array(
                // you might translate this message
                'message' => "Cannot give a sub user a server",
            );
            return new JsonResponse($data, Response::HTTP_METHOD_NOT_ALLOWED);
        }



        // Create the server database Object
        $server = new GameServer();
        $server->setServerName($this->serverNameReplacement($request->get('serverName') , $user , $template));
        $server->setOwnerId($ownerId);
        $server->setGameName($request->get('nameOfGame'));
        $server->setPlayerSlots($request->get('playerSlots'));
        $server->setRam($request->get('ram'));
        $server->setStartupCommand($request->get('startCommand'));
        $server->setUpdateCommand($request->get('serverName'));
        $server->setTemplateId($templateId);
        $server->setQueryEngine('Not Implemented');
        $server->setIp($serverIp);
        $server->setPort($request->get('updateCommand'));
        $server->setStatus("New");
        $server->setLocation("/unknown");
        //Persist the server and flush to update the DB.

        $username = $user->getUsername();

        $server->setLocation("/usr/local/sp/users/$username/$serverIp.$port");
        $em->persist($server);

        try{
            $em->flush();
            $message = "Server Created";
            $status = 201;
        }catch(\Exception $e)
        {
            $message = "error occoured with message " . $e->getMessage();
            $status = 500;

            $data = array(
                // you might translate this message
                'message' => $message,
            );

            return new JsonResponse($data, $status);
        }

        // Lets work on the actual server now!
        $enc_service = $this->getEncryptionService();

        // Create the needed services
        $networkServerService = new NetworkServerService($enc_service ,$networkServer , $em);
        $serverUserService = new ServerUserService($enc_service , $networkServer , $user , $em);

        // See if the current user already has a server account.
        if ($user->getServerUser() === 0 || $user->getServerUser() == null)
        {
            $serverUserService->createUser($user->getUsername());
        }

        // Get the server Id
        $serverId = $networkServer->getId();
        $callbackUrl = $request->getHttpHost() . "/cron/serverCallback/$serverId";

        // Actually create the server.
        $networkServerService->serverCreation($user , $networkServer , $template , $callbackUrl , $port);

        $data = array(
            // you might translate this message
            'message' => $message,
        );

        // Throw them back to the create a server page.
        return new JsonResponse($data, $status);

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
     * Generates a random port.
     *
     * Not perfect but should be able to cover 90% of use cases.
     *
     * @param $ip
     * @return int
     */
    public function findOpenPort($ip)
    {
        $port = rand('49152' , '655325');

        return $port;
    }

    /**
     * In the server name there are these possible replacements:
     *
     * {user.name} - The user who owns the servers name
     * {user.id} - The id of the user who owns the server
     * {template.name} - The name of the server template
     *
     * This function will turn these into what they should be
     *
     *
     * @param $input
     * @return string
     */
    public function serverNameReplacement($input , $user , $template)
    {
        $string_username_replace = str_replace('{user.name}' , $user->getFirstName() , $input);

        $string_id_replace = str_replace('{user.id}' , $user->getId() , $string_username_replace);

        $string_template_name_replace = str_replace('{template.name}' , $template->getTemplateName() , $string_id_replace);

        return $string_template_name_replace;


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
