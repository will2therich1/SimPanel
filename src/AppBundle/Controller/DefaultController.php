<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return new RedirectResponse('/login');
    }


    /**
     * @Route("/Maintenance" , name="Maintenance")
     */
    public function maintenanceModePage(Request $request)
    {
        $url = $request->getHttpHost();
        $data['url'] = $url;
        return $this->render('errorPages/maintenance.mode.html.twig', $data);
    }

}
