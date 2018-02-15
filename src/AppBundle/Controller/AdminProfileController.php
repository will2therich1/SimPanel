<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Service\EncryptionService;
use AppBundle\Service\GoogleAuthenticatorService;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use ApiBundle\Entity\ApiKeys;
use AppBundle\Service\SettingService;

class AdminProfileController extends Controller
{


    /**
     * @Route("/admin/account", name="AdminProfileInfo")
     */
    public function adminInfoProfilePage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        $data = [];
        $data['success'] = '';
        $data['branding'] = $settingService->getSiteInformation();


        // If user data has been posted
        if (isset($_POST['Userusername']) && $_POST['Userusername'] !== '') {
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
        return $this->render('profiles/admin/information.profile.admin.index.twig', $data);
    }

    /**
     * @Route("/admin/account/password", name="AdminProfilePassword")
     */
    public function adminPasswordProfilePage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        $data = [];
        $data['error'] = '';
        $data['success'] = '';
        $data['branding'] = $settingService->getSiteInformation();


        // Get Doctrine
        $user = $this->getUser();

        if (isset($_POST['currentPassword']) && $_POST['currentPassword'] !== '') {
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
            } else {
                $data['error'] = "Current password is not correct";
            }
        }

        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = 'Dash';
        $data['tab'] = 'ChangePass';


        // render the needed view.
        return $this->render('profiles/admin/password.profile.admin.index.twig', $data);
    }

    /**
     * @Route("/admin/account/api", name="AdminProfileAPI")
     */
    public function adminAPIProfilePage(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        $data = [];
        $data['error'] = '';
        $data['success'] = '';
        $data['branding'] = $settingService->getSiteInformation();


        // Get User
        $user = $this->getUser();

        // Get out api Keys from the database
        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('a')
            ->from('ApiBundle:ApiKeys', 'a')
            ->where('a.ownerId LIKE :id')
            ->setParameter('id', $user->getId());


        $newApiKeyName = $request->get('newApiKeyName');

        if (isset($newApiKeyName) && $newApiKeyName !== '')
        {
            $apiKeyLimit = $settingService->getSetting('apiKeyLimit')->getSettingValue();

            $queryBuilder2 = $em->createQueryBuilder();

            $apiKeyQuerys = $queryBuilder2->select('count(a)')
                                            ->from('ApiBundle:ApiKeys', 'a')
                                            ->Where('a.ownerId = :id')
                                            ->setParameter('id', $user->getId());

            $numberOfApiKeys = $apiKeyQuerys->getQuery()->getSingleScalarResult();

            if ($numberOfApiKeys < $apiKeyLimit) {

                $apiKey = new ApiKeys();
                $generatedApiKey = $this->generatePassword('20');

                $apiKey->setName($newApiKeyName);
                $apiKey->setOwnerId($this->getUser()->getId());
                $apiKey->setApiKey(password_hash($generatedApiKey, PASSWORD_DEFAULT));
                $apiKey->setLastUsed('Never');

                echo $apiKeyLimit;
                echo $numberOfApiKeys;

                $em->persist($apiKey);
                try {
                    $em->flush();

                    $data['success'] = "A new api key has been generated it is below. Note this can only be seen once! {$generatedApiKey}";
                } catch (Exception $e){
                    $data['error'] = "Creation failed with message: " . $e->getMessage();
                }
            }else{
                $data['error'] = "You have hit the maximum number of api keys";
            }

        }

        $apiKeys = $queryBuilder->getQuery()->execute();

        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = 'Dash';
        $data['tab'] = 'API';
        $data['apiKeys'] = $apiKeys;


        // render the needed view.
        return $this->render('profiles/admin/api.profile.admin.index.twig', $data);
    }

    /**
     * @Route("/admin/account/tfa", name="AdminProfileTFA")
     */
    public function adminTFAProfilePage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        $data = [];
        $data['error'] = '';
        $data['success'] = '';
        $data['branding'] = $settingService->getSiteInformation();


        // Get User
        $user = $this->getUser();


        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = 'Dash';
        $data['tab'] = 'TFA';
        $data['currentUser']['tfaStatus'] = $user->getTfaStatus();


        // render the needed view.
        return $this->render('profiles/admin/tfa.profile.admin.index.twig', $data);
    }

    /**
     * @Route("/admin/account/tfa/setup", name="AdminProfileTFASetup")
     */
    public function adminTFASetupPage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        $data = [];
        $data['error'] = '';
        $data['success'] = '';
        $data['branding'] = $settingService->getSiteInformation();

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
            return new RedirectResponse('/admin/account/tfa');
        }

        // Check if we have been posted a verification
        if (isset($_POST['tfa-code']) && $_POST['tfa-code'] !== '') {
            $verified = $tfa->verifyCode($_POST['tfa_secret'], $_POST['tfa-code']);
            if ($verified) {
                $user->setTfaSecret($_POST['tfa_secret']);
                $user->setTfaStatus(1);
                $em->persist($user);
                $em->flush();

                return new RedirectResponse('/admin/account/tfa');
            } else {
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
        return $this->render('profiles/admin/tfa.setup.profile.admin.index.twig', $data);
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

        $verify = password_verify($password, $currentPassword);

        return $verify;

    }

    /**
     * Generates a random password
     *
     * @param int $length Length of the password
     * @param bool $add_dashes Add dashes to the password
     * @param string $available_sets Rules to use
     *
     * @return bool|string
     */
    public function generatePassword($length = 9, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        if (strpos($available_sets, 'l') !== false) {
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        }
        if (strpos($available_sets, 'u') !== false) {
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }
        if (strpos($available_sets, 'd') !== false) {
            $sets[] = '23456789';
        }
        if (strpos($available_sets, 's') !== false) {
            $sets[] = '!@#$%&*?';
        }
        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++) {
            $password .= $all[array_rand($all)];
        }
        $password = str_shuffle($password);
        if (!$add_dashes) {
            return $password;
        }
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while (strlen($password) > $dash_len) {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }


}
