<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Service\NetworkServerService;
use AppBundle\Service\ServerUserService;
use AppBundle\Service\EncryptionService;
use AppBundle\Entity\User;
use AppBundle\Service\SettingService;

class TemplateCronControllerController extends Controller
{
    /**
     * Webcron for template management
     */

    /**
     * @Route("/cron/templateCallback/{id}", name="cronTemplateMaster")
     */
    function templateCallbackURL(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        $message = "Template Cron Updating";
        $response = new Response();
        $response->setContent($message);


        // get our template
        $template = $em->getRepository('AppBundle:ServerTemplate')->find($request->get('id'));

        $do = $request->get('do');

        if ($do == "steam_progress")
        {
            $progress = $request->get('percent');
            $template->setSteamPercentage($progress);
            $template->setStatus("Steam Installing");
            
        }elseif ($do == "tpl_status")
        {
            $status = $request->get('status');

            if ($status == null)
            {
                $status = $request->get('update');

            }


            if($status == 'complete')
            {
                $size = $request->get('size');
                $location = "/usr/local/sp/templates/".$request->get('id');
                $template->setSize($size);
                $status = "Completed";
            }

            $template->setStatus($status);
        }
        
        $em->persist($template);
        $em->flush();

        return $response;


    }


    /**
     * @Route("/cron/serverCallback/{id}", name="cronServerMaster")
     */
    function serverCreateCallback(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        $message = "Server Cron Updating";
        $response = new Response();
        $response->setContent($message);


        // get our template
        $server = $em->getRepository('ServerBundle:GameServer')->find($request->get('id'));

        $do = $request->get('do');

        if($do === "createsrv_status")
        {
            $server->setStatus($request->get('status'));
            $em->persist($server);
            $em->flush();
        }


        return $response;


    }


    /**
     * @return EncryptionService
     */
    public function getEncryptionService()
    {
        $encryption_params = $this->container->getParameter('encryption');
        return new EncryptionService($encryption_params);
    }
}

