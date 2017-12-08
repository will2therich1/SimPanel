<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SecurityControllerController extends Controller
{

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {

        $error = null;

        // Get the Last username
        $lastUsername = $authenticationUtils->getLastUsername();

        if (isset($_GET['login'])) {
            if ($_GET['login'] == 'failed'){
                $error = "Login Failed, Please check your credentials";
            }
        }

        return $this->render('security/login.html.twig' , array(
            'last_username' => $lastUsername,
            'error' => $error,
        ));

    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(Request $request)
    {
        $user = $this->get('security.token_storage')
            ->getToken()
            ->getUser();

        $this->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        return new RedirectResponse('login');

    }

}
