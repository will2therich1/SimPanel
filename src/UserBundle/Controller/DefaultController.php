<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\ServerTemplate;

class DefaultController extends Controller
{
    /**
     * @Route("/user", name="userIndex")
     */
    public function userIndexAction(Request $request)
    {

        $user = $this->getUser();

        $data = [];
        $data['active'] = "Dash";
        $data['user'] = $user->getUserInfo();
        // replace this example code with whatever you need
        return $this->render('userBundle/dashboardpage.html.twig' , $data);
    }

}
