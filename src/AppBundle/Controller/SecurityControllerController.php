<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use AppBundle\Service\SettingService;

class SecurityControllerController extends Controller
{

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        // Set this variable before we continue.
        $error = null;


        // We will make sure they are not already logged in
        $user = $this->getUser();

        // If logged in we will redirect to our check pages
        if ($user) {
            return new RedirectResponse('login_check');
        }

        // We will check to see if we have been passed any errors.
        if (isset($_GET['login'])) {
            if ($_GET['login'] == 'failed') {
                $error = "Login Failed, Please check your credentials & your TFA Code if applicable \n Your account may also be deactive!";
            }
        }

        $data = [];

        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);

        $data['branding'] = $settingService->getSiteInformation();

        return $this->render('security/login.html.twig', $data);

    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(Request $request)
    {

        // We will set the security token to null
        $this->get('security.token_storage')->setToken(null);
        // Now we shall invalidate the session!
        $request->getSession()->invalidate();

        // Finally send them back to the login page
        return new RedirectResponse('/login');

    }

    /**
     * This will check the users role and redirect to the relevant areas.
     *
     * @Route("/login_check", name="loginCheck")
     */
    public function loginRedirect(Request $request)
    {
        // Get the user and their roles
        $roles = $this->getUser()->getRoles();

        // Check if they have the admin role
        if (isset($roles['USER_ROLE_2'])) {
            // Verify the admin role
            if ($roles['USER_ROLE_2'] === 'ROLE_ADMIN') {
                // If they are an admin then we will take them to the admin area
                return new RedirectResponse('/admin');
            }
        } else {
            // Else if they are a user then we take them to the user area
            return new RedirectResponse('/user');
        }

        // Incase nothing matches than to the login page with you!
        return new RedirectResponse('/login');

    }

}
