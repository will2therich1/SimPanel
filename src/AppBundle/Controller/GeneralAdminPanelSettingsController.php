<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Settings;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\SettingService;

class GeneralAdminPanelSettingsController extends Controller
{

    /**
     * @Route("/admin/settings/general/admin" , name="GeneralAdminSettings")
     */
    public function adminGeneralSettings()
    {
        // Get our setting service
        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);


        // Create our Data Array
        $data = [];




        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = 'GeneralSettings';
        $data['tab'] = 'General';
        $data['branding'] = $settingService->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';

        // Deal with Ajax requests!

        return $this->render('settings/general/admin/admin.general.settings.tab.general.html.twig', $data);


    }


}
