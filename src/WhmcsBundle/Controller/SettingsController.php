<?php

namespace WhmcsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Settings;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\SettingService;
use AppBundle\Service\EncryptionService;
use GuzzleHttp\Client;

class SettingsController extends Controller
{
    /**
     * @Route("/admin/settings/general/admin/whmcs" , name="GeneralAdminSettingsWhmcs")
     */
    public function adminGeneralSettingsWHMCSIntegration(Request $request)
    {
        // Get our setting service
        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);


        // Create our Data Array
        $data = [];

        $parameters = $this->getParameter('whmcs');


        $data['whmcsParameters'] = $parameters;
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = 'GeneralSettings';
        $data['tab'] = 'WHMCS';
        $data['branding'] = $settingService->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';

        return $this->render('settings/general/admin/admin.general.settings.tab.whmcs.html.twig', $data);


    }
    /**
     * @Route("/admin/settings/general/admin/whmcs/connection/test" , name="GeneralAdminSettingsWhmcsTest")
     */
    public function adminGeneralSettingsWHMCSIntegrationTest(Request $request)
    {
        // Get our setting service
        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);


        // Create our Data Array
        $data = [];

        $whcmsParameters = $this->getParameter('whmcs');

        $client = new Client();

        $whmcsUrl = $whcmsParameters['whmcs_url'];
        $whmcsApiUrl = $whmcsUrl . '/includes/api.php';


        $postfields = array(
            'identifier' => $whcmsParameters['whmcs_identifier'],
            'secret' => $whcmsParameters['whmcs_secret'],
            'action' => 'GetActivityLog',
            'responsetype' => 'json',
        );


        try {
            $apiRequest = $client->post($whmcsApiUrl, ['form_params' => $postfields]);

            $apiResult = $apiRequest->getBody()->getContents();

            $apiResultArray = json_decode($apiResult);

            if ($apiResultArray->result == "success")
            {
                $data['success'] = "API Connected";
            }


        }catch (\Exception $e){
            $data['error'] = "An error occoured with the following message" . $e->getMessage();
        }

        $data['whmcsParameters'] = $whcmsParameters;
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = 'GeneralSettings';
        $data['tab'] = 'WHMCS';
        $data['branding'] = $settingService->getSiteInformation();

        return $this->render('settings/general/admin/admin.general.settings.tab.whmcs.html.twig', $data);


    }


}
