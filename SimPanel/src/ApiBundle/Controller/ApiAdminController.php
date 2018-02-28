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
use AppBundle\Entity\User;

class ApiAdminController extends Controller
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
     * @Rest\Get("/api/v1/admins")
     */
    public function adminApiList(Request $request)
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
            ->from('AppBundle:User', 'u')
            ->Where('u.admin = 1')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $result = $queryBuilder->getQuery()->execute();


        foreach ($result as $user )
        {
            $data["user_".$user->getId()]['id'] = $user->getId();
            $data["user_".$user->getId()]['first_name'] = $user->getFirstName();
            $data["user_".$user->getId()]['last_name'] = $user->getLastName();
            $data["user_".$user->getId()]['username'] = $user->getUsername();
            $data["user_".$user->getId()]['email'] = $user->getEmail();
            $data["user_".$user->getId()]['status'] = $user->getStatus();
            $data["user_".$user->getId()]['roles'] = $user->getRoles();
            $data["user_".$user->getId()]['tfa_status'] = $user->getTfaStatus();


        }

        return new JsonResponse($data, 200);

    }

    /**
     * @Rest\Get("/api/v1/admins/{id}")
     */
    public function adminApiSpecific(Request $request)
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

        $user = $em->getRepository('AppBundle:User')->find($request->get('id'));


        if ($user == null) {
            $data = array(
                // you might translate this message
                'message' => "No admin found with the id of {$request->get('id')}",
            );

            return new JsonResponse($data, Response::HTTP_NOT_FOUND);
        } else {
            if ($user->getAdmin() == 0) {
                $data = array(
                    // you might translate this message
                    'message' => "No admin found with the id of {$request->get('id')}, There is a user with this id",
                );

                return new JsonResponse($data, Response::HTTP_NOT_FOUND);
            }
        }

        $data["user_" . $user->getId()]['id'] = $user->getId();
        $data["user_" . $user->getId()]['first_name'] = $user->getFirstName();
        $data["user_" . $user->getId()]['last_name'] = $user->getLastName();
        $data["user_" . $user->getId()]['username'] = $user->getUsername();
        $data["user_" . $user->getId()]['email'] = $user->getEmail();
        $data["user_" . $user->getId()]['status'] = $user->getStatus();
        $data["user_" . $user->getId()]['roles'] = $user->getRoles();
        $data["user_" . $user->getId()]['tfa_status'] = $user->getTfaStatus();


        return new JsonResponse($data, 200);

    }

    /**
     * @Rest\Put("/api/v1/admins/{id}")
     */
    public function postAdminUpdate(Request $request)
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

        $user = $em->getRepository('AppBundle:User')->find($request->get('id'));



        if ($user == null) {
            $data = array(
                // you might translate this message
                'message' => "No admin found with the id of {$request->get('id')}",
            );

            return new JsonResponse($data, Response::HTTP_NOT_FOUND);
        } else {
            if ($user->getAdmin() == 0) {
                $data = array(
                    // you might translate this message
                    'message' => "No admin found with the id of {$request->get('id')}, There is a user with this id",
                );

                return new JsonResponse($data, Response::HTTP_NOT_FOUND);
            }
        }

        $user->setFirstName($request->get('first_name'));
        $user->setLastName($request->get('last_name'));
        $user->setEmail(filter_var($request->get('email') , FILTER_VALIDATE_EMAIL));
        $user->setUsername($request->get('username'));
        $user->setStatus($request->get('status'));

        $em->persist($user);

        try{
            $em->flush();
            $message = "User Updated";
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
     * @Rest\Post("/api/v1/admins/create/")
     */
    public function adminAPICreate(Request $request)
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

        $user = new User();
        $user->setFirstName($request->get('firstName'));
        $user->setLastName($request->get('lastName'));
        $user->setUsername($request->get('name'));
        $user->setEmail(filter_var($request->get('email') , FILTER_VALIDATE_EMAIL));
        $user->setTfaStatus(0);
        $user->setStatus($request->get('status'));
        $user->setAdmin(1);

        $password = $user->generatePassword();
        $hashed_password = password_hash($password , PASSWORD_DEFAULT);

        $user->setPassword($hashed_password);

        $em->persist($user);

        try{
            $em->flush();
            $message = "Admin Created";
            $status = 201;
            $this->userCreationEmail($user->getEmail() , $user->getUsername() , $password);
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

    public function userCreationEmail($email, $username, $password)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Account Created at SimPanel')
            ->setFrom('no-reply@servers4all.co.uk')
            ->setTo($email)
            ->setBody(
                $this->renderView(
                    'emails/user/user.creation.email.html.twig',
                    array(
                        'username' => $username,
                        'password' => $password,
                        'loginurl' => 'https://poisonpanel.servers4all.co.uk'
                    )
                ), 'text/html'
            );
        $this->get('mailer')->send($message);

    }


}
