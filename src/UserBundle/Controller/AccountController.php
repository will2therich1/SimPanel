<?php

namespace UserBundle\Controller;

use AppBundle\Service\GoogleAuthenticatorService;
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


        // replace this example code with whatever you need
        return $this->render('userBundle/accountSettings/user.settings.general.security.tfa.setup.main.html.twig' , $data);
    }



}
