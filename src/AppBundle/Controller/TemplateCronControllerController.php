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
        error_log($request->getContent());

        $message = "Cron test";
        $response = new Response();
        $response->setContent($message);


        // get our template
        $template = $this->getDoctrine()->getManager()->getRepository('AppBundle:ServerTemplate')->find($request->get('id'));

        $do = $request->get('do');

        if ($do == "steam_progress")
        {
            $progress = $request->get('percent');
            $template->setSteamPercentage($progress);
            $template->setStatus("Steam Installing");
            
        }elseif ($do == "tpl_status")
        {
            $status = $request->get('started');
            $template->setStatus($status);
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($template);
        $em->flush();

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

