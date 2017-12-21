<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Settings;
use Symfony\Component\HttpFoundation\Request;

class GeneralUserPanelSettingsController extends Controller
{


    /**
     * @Route("/admin/settings/general/user/branding" , name="BrandingUserSettings")
     */
    public function userGeneralBrandingSettings(Request $request)
    {
        // Create our Data Array
        $data = [];

        if ($request->getMethod() == 'POST') {
            $this->setSetting('panelName', $_POST['PanelName']);
            $this->setSetting('panelNamePart1', $_POST['PanelNamePart1']);
            $this->setSetting('panelNamePart2', $_POST['PanelNamePart2']);
            $this->setSetting('PanelNameShortPart1', $_POST['PanelNameShortPart1']);
            $this->setSetting('PanelNameShortPart2', $_POST['PanelNameShortPart2']);
        }

        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = 'GeneralSettings';
        $data['tab'] = 'Branding';
        $data['branding'] = $this->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';

        return $this->render('settings/general/user/user.general.settings.tab.branding.html.twig', $data);


    }

    /**
     * @Route("/admin/settings/general/user" , name="GeneralUserSettings")
     */
    public function userGeneralSettings()
    {
        // Create our Data Array
        $data = [];

        $em = $this->getDoctrine()->getManager();

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
        $data['branding'] = $this->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';

        // Deal with Ajax requests!

        return $this->render('settings/general/user/user.general.settings.tab.general.html.twig', $data);


    }


    /**
     * Returns the Setting Object!
     *
     * If the setting dosen't exist then it will be created.
     *
     * @param $settingName
     *          Name of the Setting
     * @return Settings|mixed
     */
    public function getSetting($settingName)
    {
        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings');
        $query = $settings->createQueryBuilder('s');
        $result = $query->select('s.id')
            ->where('s.settingName = :setting')
            ->setParameter('setting', $settingName)
            ->getQuery()
            ->execute();

        if (empty($result)) {
            $newSetting = new Settings();
            $newSetting->setSettingName($settingName);
            $newSetting->setSettingValue(0);
            $newSetting->setSettingUpdatedTime(new \DateTime());

            $this->getDoctrine()->getManager()->persist($newSetting);
            $this->getDoctrine()->getManager()->flush();

            return $newSetting;
        }

        $result = $result[0];
        $id = $result['id'];


        $returnObject = $this->getDoctrine()->getRepository('AppBundle:Settings')->find($id);

        return $returnObject;
    }

    public function setSetting($settingName, $settingValue)
    {
        $setting = $this->getSetting($settingName);

        $setting->setSettingValue($settingValue);
        $em = $this->getDoctrine()->getManager();
        $em->persist($setting);
        $em->flush();

        return;

    }

    public function getSiteInformation()
    {

        $returnArray = [];
        $returnArray['panelName'] = $this->getSetting('PanelName')->getSettingValue();
        $returnArray['panelNamePart1'] = $this->getSetting('PanelNamePart1')->getSettingValue();
        $returnArray['PanelNamePart2'] = $this->getSetting('PanelNamePart2')->getSettingValue();
        $returnArray['PanelNameShortPart1'] = $this->getSetting('PanelNameShortPart1')->getSettingValue();
        $returnArray['PanelNameShortPart2'] = $this->getSetting('PanelNameShortPart2')->getSettingValue();

        return $returnArray;
    }


}
