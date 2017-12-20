<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Service\EncryptionService;
use AppBundle\Service\GoogleAuthenticatorService;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdminProfileController extends Controller
{


    /**
     * @Route("/admin/account", name="AdminProfileInfo")
     */
    public function adminInfoProfilePage()
    {
        $data = [];
        $data['success'] = '';
        $data['branding'] = $this->getSiteInformation();

        $em = $this->getDoctrine()->getManager();


        if (isset($_POST['Userusername']) && $_POST['Userusername'] !== ''){
            $user = $this->getUser();

            $user->setUsername($_POST['Userusername']);
            $user->setEmail($_POST['email']);
            $user->setFirstName($_POST['first_name']);
            $user->setLastName($_POST['last_name']);
            $data['success'] = 'User Updated';
            $em->persist($user);
            $em->flush();
        }

        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = 'Dash';
        $data['tab'] = 'Info';
        $data['error'] = '';





        // replace this example code with whatever you need
        return $this->render('profiles/admin/information.profile.admin.index.twig' , $data);
    }

    /**
     * @Route("/admin/account/password", name="AdminProfilePassword")
     */
    public function adminPasswordProfilePage()
    {
        $data = [];
        $data['error'] = '';
        $data['success'] = '';
        $data['branding'] = $this->getSiteInformation();


        // Get Doctrine
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        if (isset($_POST['currentPassword']) && $_POST['currentPassword'] !== '')
        {
            // Set out variables!
            $currentPassword = $_POST['currentPassword'];
            $newPassword = $_POST['newPassword'];
            $newPasswordConfirm = $_POST['newPasswordConfirmation'];

            // Verify the current password
            if ($this->verifyPassword($currentPassword)) {
                // If correct then we check the two new passwords match
                if ($newPassword === $newPasswordConfirm) {
                    $hash = password_hash($newPassword, PASSWORD_DEFAULT);

                    $user->setPassword($hash);
                    $em->persist($user);
                    $em->flush();

                    $data['success'] = 'Password has been updated';

                } else {
                    $data['error'] = "Provided Passwords do not match";

                }
            }else{
                $data['error'] = "Current password is not correct";
            }
        }

        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = 'Dash';
        $data['tab'] = 'ChangePass';


        // render the needed view.
        return $this->render('profiles/admin/password.profile.admin.index.twig' , $data);
    }

    /**
     * @Route("/admin/account/api", name="AdminProfileAPI")
     */
    public function adminAPIProfilePage()
    {
        $data = [];
        $data['error'] = '';
        $data['success'] = '';
        $data['branding'] = $this->getSiteInformation();


        // Get Doctrine
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $encryption_params = $this->container->getParameter('encryption');


        if (isset($_POST['currentAPIKey']))
        {
            $newApiKey = $this->generatePassword(20);
            $user->setApiKey($newApiKey , new EncryptionService($encryption_params));
            $data['success'] = 'API Key updated';

            $em->persist($user);
            $em->flush();
        }

        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = 'Dash';
        $data['tab'] = 'API';
        $data['currentUser']['apiKey'] = $user->getApiKey(new EncryptionService($encryption_params));


        // render the needed view.
        return $this->render('profiles/admin/api.profile.admin.index.twig' , $data);
    }

    /**
     * @Route("/admin/account/tfa", name="AdminProfileTFA")
     */
    public function adminTFAProfilePage()
    {
        $data = [];
        $data['error'] = '';
        $data['success'] = '';
        $data['branding'] = $this->getSiteInformation();


        // Get Doctrine
        $user = $this->getUser();


        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = 'Dash';
        $data['tab'] = 'TFA';
        $data['currentUser']['tfaStatus'] = $user->getTfaStatus();


        // render the needed view.
        return $this->render('profiles/admin/tfa.profile.admin.index.twig' , $data);
    }

    /**
     * @Route("/admin/account/tfa/setup", name="AdminProfileTFASetup")
     */
    public function adminTFASetupPage()
    {
        $data = [];
        $data['error'] = '';
        $data['success'] = '';
        $data['branding'] = $this->getSiteInformation();

        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        // Get Google Authenticator
        $tfa = new GoogleAuthenticatorService();

        // Create our TFA Secret & Barcode.
        $tfaSecret = $tfa->createSecret();
        $tfaBarcode = $tfa->getQRCodeGoogleUrl("PoisonPanel TFA" ,$tfaSecret);


        // If TFA is active - deactivate
        if ($user->getTfaStatus() == 1)
        {
            $user->setTfaStatus(0);
            $em->persist($user);
            $em->flush();
            return new RedirectResponse('/admin/account/tfa');
        }

        // Check if we have been posted a verification
        if (isset($_POST['tfa-code']) && $_POST['tfa-code'] !== '')
        {
            $verified = $tfa->verifyCode($_POST['tfa_secret'] , $_POST['tfa-code']);
            if ($verified)
            {
                $user->setTfaSecret($_POST['tfa_secret']);
                $user->setTfaStatus(1);
                $em->persist($user);
                $em->flush();

                return new RedirectResponse('/admin/account/tfa');
            }else{
                $tfaSecret = $_POST['tfa_secret'];
            }

        }



        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = 'Dash';
        $data['tab'] = 'TFA';
        $data['tfa']['secret'] = $tfaSecret;
        $data['tfa']['barcode'] = $tfaBarcode;

        // render the needed view.
        return $this->render('profiles/admin/tfa.setup.profile.admin.index.twig' , $data);
    }


    /**
     * Checks if the password is correct for the current user, useful for additional security checks
     *
     * @param $password
     *        Password to verify against.
     * @return bool
     */
    public function verifyPassword($password)
    {
        $user = $this->getUser();
        $currentPassword = $user->getPassword();

        $verify = password_verify($password , $currentPassword);

        return $verify;

    }

    /**
     * Generates a random password
     *
     * @param int    $length         Length of the password
     * @param bool   $add_dashes     Add dashes to the password
     * @param string $available_sets Rules to use
     *
     * @return bool|string
     */
    public function generatePassword($length = 9, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        if(strpos($available_sets, 'l') !== false) {
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        }
        if(strpos($available_sets, 'u') !== false) {
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }
        if(strpos($available_sets, 'd') !== false) {
            $sets[] = '23456789';
        }
        if(strpos($available_sets, 's') !== false) {
            $sets[] = '!@#$%&*?';
        }
        $all = '';
        $password = '';
        foreach($sets as $set)
        {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for($i = 0; $i < $length - count($sets); $i++) {
            $password .= $all[array_rand($all)];
        }
        $password = str_shuffle($password);
        if(!$add_dashes) {
            return $password;
        }
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while(strlen($password) > $dash_len)
        {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }


    /**
     * Gets the site information and returns this
     *
     * @return array
     */
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

    /**
     * Returns the Setting Object!
     *
     * If the setting dosen't exist then it will be created.
     *
     * @param $settingName
     *          Name of the Setting
     * @return Settings|mixed
     */
    public function getSetting($settingName )
    {
        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings');
        $query = $settings->createQueryBuilder('s');
        $result = $query->select('s.id')
            ->where('s.settingName = :setting')
            ->setParameter('setting' , $settingName)
            ->getQuery()
            ->execute();

        if (empty($result))
        {
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
}
