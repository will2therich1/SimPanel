<?php

namespace WhmcsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Settings;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\SettingService;
use AppBundle\Service\EncryptionService;
use GuzzleHttp\Client;
use WhmcsBundle\Service\WhmcsService;

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
    /**
     * @Route("/user/support" , name="userSupportTicket")
     */
    public function userCreateSupportTicket(Request $request)
    {
        // Get our setting service
        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);
        $message = null;

        // Create our Data Array
        $data = [];

        $parameters = $this->getParameter('whmcs');

        $whmcsService = new WhmcsService($this->getUser() , $parameters);
        $departments = $whmcsService->getWhmcsDepartments();

        if ($request->getMethod() === "POST")
        {
            $client = $whmcsService->getWhmcsUser();

            $ticketData = [];
            $ticketData['departmentId'] = $request->get('department');
            $ticketData['subject'] = $request->get('ticketSubject');
            $ticketData['message'] = $request->get('ticketDescription');
            $ticketData['priority'] = $request->get('priority');
            $ticketData['clientId'] = $client['id'];
            $ticketData['name'] = $client['firstname'] . " " . $client['lastname'];
            $ticketData['email'] = $client['email'];

            $message = $whmcsService->createWhmcsTicket($ticketData);
            $data['success'] = $message;

        }
        $user = $this->getUser();


        $data['whmcsParameters'] = $parameters;
        $data['user'] = $this->getUser()->getUserInfo();
        $data['whmcsActive'] = $user->getWhmcsStatus();
        $data['globalWhmcs'] = $parameters['whmcs_status'];
        $data['active'] = 'Support';
        $data['tab'] = 'WHMCS';
        $data['departments'] = $departments;
        $data['site'] = $settingService->getSiteInformation();

        return $this->render('userBundle/user.create.support.ticket.twig', $data);


    }

}
