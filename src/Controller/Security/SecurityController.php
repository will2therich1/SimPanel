<?php
/**
 * Security controller dealing with login & logout & TFA
 *
 * @author William Rich
 * @copyright https://servers4all.documize.com/s/Wm5Pm0A1QQABQ1xw/simpanel/d/WnDQ5EA1QQABQ154/simpanel-license
 */
namespace App\Controller\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SecurityController extends Controller
{

    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        // Get the error from the authentication utility
        $error = $authenticationUtils->getLastAuthenticationError();

        // Get the shortned error message not the full one
        if ($error !== null) {
            $error = $error->getMessage();
        }

        // Get the last username from the authentication utility
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/index.html.twig', [
          'controller_name' => 'SecurityController',
          'error' => $error,
          'lastUsername' => $lastUsername,
        ]);
    }

    public function logout(Request $request)
    {

        // We will set the security token to null
        $this->get('security.token_storage')->setToken(null);
        // Now we shall invalidate the session!
        $request->getSession()->invalidate();

        return new RedirectResponse('/login');
    }
}
