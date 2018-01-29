<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\ServerTemplate;
use AppBundle\Service\SettingService;

class AccountController extends Controller
{
    /**
     * @Route("/user/settings", name="userSettingsMain")
     */
    public function userIndexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);


        $user = $this->getUser();

        $data = [];
        $data['active'] = "Dash";
        $data['user'] = $user->getUserInfo();
        $data['site'] = $settingService->getSiteInformation();
        // replace this example code with whatever you need
        return $this->render('userBundle/accountSettings/user.settings.main.html.twig' , $data);
    }



}
