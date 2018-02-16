<?php

namespace UserBundle\Controller;

use AppBundle\Service\GoogleAuthenticatorService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\ServerTemplate;
use AppBundle\Service\SettingService;
use GuzzleHttp\Client;

class AccountController extends Controller
{
    /**
     * @Route("/user/settings", name="userSettingsMain")
     */
    public function userIndexAction(Request $request)
    {
        $data = [];

        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);
        $user = $this->getUser();


        if ($request->getMethod() == "POST")
        {
            $user->setFirstName($request->get('firstname'));
            $user->setLastName($request->get('lastname'));
            $user->setEmail($request->get('emailAddr'));
            $em->persist($user);
            $em->flush();

            $data['success'] = "Details Updated";

        }



        $data['active'] = "AccountSettings";
        $data['user'] = $user->getUserInfo();
        $data['site'] = $settingService->getSiteInformation();
        $data['tab'] = "General";

        // replace this example code with whatever you need
        return $this->render('userBundle/accountSettings/user.settings.main.html.twig' , $data);
    }

    /**
     * @Route("/user/settings/general/security", name="userSettingsGeneralSecurity")
     */
    public function userSettingsGeneralSecurity(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);


        $user = $this->getUser();

        $data = [];
        $data['active'] = "Dash";
        $data['user'] = $user->getUserInfo();
        $data['site'] = $settingService->getSiteInformation();

        if ($request->getMethod() == "POST")
        {
            $em = $this->getDoctrine()->getManager();
            $action = $request->get('do');
            if ($action == "passwordUpdate")
            {
                $currentPassword = $request->get('currentPassword');
                $newPassword = $request->get('newPassword');
                $newPasswordConfirm = $request->get('newPasswordConfirm');

                $verify = password_verify($currentPassword , $user->getPassword());

                if ($verify){
                    if ($newPassword === $newPasswordConfirm){
                        $user->setPassword(password_hash($newPasswordConfirm , PASSWORD_DEFAULT));
                        $em->persist($user);
                        $em->flush();

                        $data['success'] = "Password Updated";
                    }else{
                        $data['error'] = "New Passwords do not match!";
                    }

                }else{
                    $data['error'] = "Current Password dosen't match";
                }

            }

        }
        $data['active'] = "AccountSettings";
        $data['tfaStatus'] = $user->getTfaStatus();
        $data['tab'] = '';
        // replace this example code with whatever you need
        return $this->render('userBundle/accountSettings/user.settings.general.security.main.html.twig' , $data);
    }


    /**
     * @Route("/user/settings/general/security/tfa/setup", name="userTfaSetup")
     */
    public function userSettingsGeneralSecurityTFA(Request $request)
    {       // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        // Get User
        $user = $this->getUser();

        // Get Google Authenticator
        $tfa = new GoogleAuthenticatorService();

        // Create our TFA Secret & Barcode.
        $tfaSecret = $tfa->createSecret();

        $serviceSetting = $settingService->getSetting('PanelName');
        $serviceName = $serviceSetting->getSettingValue();
        $tfaBarcode = $tfa->getQRCodeGoogleUrl($serviceName." TFA", $tfaSecret);


        // If TFA is active - deactivate
        if ($user->getTfaStatus() == 1) {

            $user->setTfaStatus(0);
            $em->persist($user);
            $em->flush();
            return new RedirectResponse('/user/settings/general/security');
        }

        // Check if we have been posted a verification
        if (isset($_POST['tfaCode']) && $_POST['tfaCode'] !== '') {

            $verified = $tfa->verifyCode($_POST['tfaSecret'], $_POST['tfaCode']);
            if ($verified) {
                $user->setTfaSecret($_POST['tfaSecret']);
                $user->setTfaStatus(1);
                $em->persist($user);
                $em->flush();

                return new RedirectResponse('/user/settings/general/security');
            } else {
                $tfaSecret = $_POST['tfaSecret'];
            }

        }

        $data = [];
        $data['active'] = "AccountSettings";
        $data['user'] = $user->getUserInfo();
        $data['site'] = $settingService->getSiteInformation();
        $data['tfa']['secret'] = $tfaSecret;
        $data['tfa']['barcode'] = $tfaBarcode;
        $data['tab'] = "General";


        // replace this example code with whatever you need
        return $this->render('userBundle/accountSettings/user.settings.general.security.tfa.setup.main.html.twig' , $data);
    }


    /**
     * @Route("/user/settings/whmcs", name="userWhmcsSetup")
     */
    public function userSettingsWhmcs(Request $request)
    {

        $data = [];


        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        // Get User
        $user = $this->getUser();

        $whmcsSettings = $this->getParameter('whmcs');

        $userWhmcs = [];
        $userWhmcsEnabled = $user->getWhmcsStatus();
        $userWhmcsEmail = $user->getWhmcsEmail();

        $userWhmcs['enabled'] = $userWhmcsEnabled;
        $userWhmcs['email'] = $userWhmcsEmail;

        if($request->getMethod() === "POST") {



            if ($request->get('do') === "activateWHMCS")
            {

                $client = new Client();

                $whmcsUrl = $whmcsSettings['whmcs_url'];
                $whmcsApiUrl = $whmcsUrl . '/includes/api.php';


                $postfields = array(
                    'identifier' => $whmcsSettings['whmcs_identifier'],
                    'secret' => $whmcsSettings['whmcs_secret'],
                    'action' => 'GetClients',
                    'search' => $request->get('whmcsEmail'),
                    'responsetype' => 'json',
                );


                try {
                    $apiRequest = $client->post($whmcsApiUrl, ['form_params' => $postfields]);

                    $apiResult = $apiRequest->getBody()->getContents();

                    $apiResultArray = json_decode($apiResult);

                    // Get through the maze of objects and variables
                    $apiResultArray = get_object_vars($apiResultArray);
                    $apiResultClients = get_object_vars($apiResultArray['clients']);
                    $apiResultClient = $apiResultClients['client'];
                    $apiClient = get_object_vars($apiResultClient[0]);


                    if ($apiClient['email'] === $request->get('whmcsEmail'))
                    {
                        $loginValidateRequest = array(
                            'identifier' => $whmcsSettings['whmcs_identifier'],
                            'secret' => $whmcsSettings['whmcs_secret'],
                            'action' => 'ValidateLogin',
                            'email' => $request->get('whmcsEmail'),
                            'password2' => $request->get('whmcsPassword'),
                            'responsetype' => 'json',
                        );

                        $apiLoginValidation = $client->post($whmcsApiUrl, ['form_params' => $loginValidateRequest]);
                        $apiLoginValidationResult = $apiLoginValidation->getBody()->getContents();
                        $apiLoginValidationResultArray = get_object_vars(\GuzzleHttp\json_decode($apiLoginValidationResult));


                        if ($apiLoginValidationResultArray['result'] === "success")
                        {
                            $user->setWhmcsStatus(1);
                            $user->setWhmcsEmail($request->get('whmcsEmail'));
                            $em->persist($user);
                            $em->flush();

                            $data['success'] = "API Account now linked!";

                        }else{
                            $data['error'] = "Logging into WHMCS Failed with the following message: " . $apiLoginValidationResultArray['message'];
                        }

                    }


                }catch (\Exception $e){
                    $data['error'] = "An error occoured with the following message" . $e->getMessage();
                }


            }elseif($request->get('do') === "deactivateWHMCS")
            {
                $user->setWhmcsStatus(0);
                $user->setWhmcsEmail("");
                $em->persist($user);
                $em->flush();
            }

        }

        

        $data['whmcsSettings'] = $whmcsSettings;
        $data['whmcsUser'] = $userWhmcs;
        $data['active'] = "AccountSettings";
        $data['user'] = $user->getUserInfo();
        $data['site'] = $settingService->getSiteInformation();
        $data['tab'] = "whmcs";

        // replace this example code with whatever you need
        return $this->render('userBundle/accountSettings/user.settings.general.security.whmcs.main.html.twig' , $data);
    }



}
