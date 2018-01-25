<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Settings;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\SettingService;

class GeneralUserPanelSettingsController extends Controller
{


    /**
     * @Route("/admin/settings/general/user/branding" , name="BrandingUserSettings")
     */
    public function userGeneralBrandingSettings(Request $request)
    {
        // Get our setting service
        $settingService = new SettingService($this->getDoctrine()->getManager());

        // Create our Data Array
        $data = [];

        if ($request->getMethod() == 'POST') {
            $settingService->setSetting('panelName', $_POST['PanelName']);
            $settingService->setSetting('panelNamePart1', $_POST['PanelNamePart1']);
            $settingService->setSetting('panelNamePart2', $_POST['PanelNamePart2']);
            $settingService->setSetting('PanelNameShortPart1', $_POST['PanelNameShortPart1']);
            $settingService->setSetting('PanelNameShortPart2', $_POST['PanelNameShortPart2']);
            $settingService->setSetting('TermsAndConditions', $_POST['TermsAndConditions']);
        }

        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = 'GeneralSettings';
        $data['tab'] = 'Branding';
        $data['branding'] = $settingService->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';

        return $this->render('settings/general/user/user.general.settings.tab.branding.html.twig', $data);


    }

    /**
     * @Route("/admin/settings/general/user" , name="GeneralUserSettings")
     */
    public function userGeneralSettings()
    {
        // Get our setting service
        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);


        // Create our Data Array
        $data = [];


        if (isset($_POST['action']) && $_POST['action'] !== '') {
            $action = $_POST['action'];

            if ($action === 'enableMaintenanceMode') {
                $maintenance = $this->getSetting('Maintenance');
                $maintenance->setSettingValue('1');
                $em->persist($maintenance);
                $em->flush();

                return true;

            } elseif ($action === 'disableMaintenanceMode') {
                $maintenance = $this->getSetting('Maintenance');
                $maintenance->setSettingValue('0');
                $em->persist($maintenance);
                $em->flush();

                return true;

            }

        }


        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = 'GeneralSettings';
        $data['tab'] = 'General';
        $data['branding'] = $settingService->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';

        // Deal with Ajax requests!

        return $this->render('settings/general/user/user.general.settings.tab.general.html.twig', $data);


    }





}
