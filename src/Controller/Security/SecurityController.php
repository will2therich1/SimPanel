<?php
/**
 * Security controller dealing with login & logout & TFA
 *
 * @author William Rich
 * @copyright https://servers4all.documize.com/s/Wm5Pm0A1QQABQ1xw/simpanel/d/WnDQ5EA1QQABQ154/simpanel-license
 */
namespace App\Controller\Security;

use App\Service\Core\DataCompiler;
use App\Service\Security\GoogleAuthenticatorService;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SecurityController extends Controller
{

    /**
     * Handles system logins
     *
     * @param Request $request
     * @param AuthenticationUtils $authenticationUtils
     * @param DataCompiler $dataCompiler
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils , DataCompiler $dataCompiler)
    {
        // Get the error from the authentication utility
        $error = $authenticationUtils->getLastAuthenticationError();

        // Get the shortned error message not the full one
        if ($error !== null) {
            $error = $error->getMessage();
        }

        // Get the last username from the authentication utility
        $lastUsername = $authenticationUtils->getLastUsername();

        $dataArray = $dataCompiler->createDataArray('login');
        $dataArray['error'] = $error;
        $dataArray['lastUsername'] = $lastUsername;

        return $this->render('security/index.html.twig', $dataArray);
    }

    /**
     * Handles system logouts
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request)
    {
        // We will set the security token to null
        $this->get('security.token_storage')->setToken(null);
        // Now we shall invalidate the session!
        $request->getSession()->invalidate();

        return new RedirectResponse('/login');
    }

    /**
     * This will check the users role and redirect to the relevant areas.
     */
    public function loginRedirect(Request $request)
    {

        $user = $this->getUser();

        // Get the user roles, and TFA status
        $roles = $user->getRoles();
        $session = $request->getSession();
        $tfaStatus = $user->getTfaStatus();

        if ($tfaStatus == 1) {
            $tfaConfirmed = $session->get('tfaConfirmed');
            if ($tfaConfirmed !== 1)
            {
                return new RedirectResponse('/tfa');
            }
        }else{
            $session->set('tfaConfirmed' , 1);
        }

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

    /**
     * Authenticates a users TFA Status
     *
     * @param Request $request
     * @param GoogleAuthenticatorService $tfaService
     * @param DataCompiler $dataCompiler
     *
     * @throws - When something goes wrong w/caching
     *
     * @return \Symfony\Component\HttpFoundation\Response | RedirectResponse
     */
    public function tfaVerificationPage(Request $request, GoogleAuthenticatorService $tfaService , DataCompiler $dataCompiler)
    {
        // Get vars
        $user = $this->getUser();
        $session = $request->getSession();
        $tfaSession = $session->get('tfaConfirmed');

        if ($tfaSession == 1) $this->redirectToRoute('login_check');
        if ($user->getTfaStatus() == 0) $this->redirectToRoute('login_check');
        $dataArray = $dataCompiler->createDataArray('tfa');

        $tfaForm = $this->createFormBuilder()
          ->add('TFA_Code', TextType::class , array(
            'attr' => array(
              'class' => 'input-material',
              'id' => 'tfa_code',
              'placeholder' => 'TFA Code',
              'required' => true,
            ),
            'label' => 'TFA Code',
            'required' => true,
          ))
          ->add('Verify', SubmitType::class , array(
            'attr' => array(
              'class' => 'btn btn-primary',
              'id' => 'inputHorizontalSuccess',
              'placeholder' => '',
              'style' => 'margin-top: 15px;',
            ),
          ))
          ->getForm();

        $tfaForm->handleRequest($request);

        if ($tfaForm->isSubmitted() && $tfaForm->isValid()) {
            $formData = $tfaForm->getData();

            $tfaSecret = $user->getTfaSecret();
            $verify = $tfaService->verifyCode($tfaSecret, $formData['TFA_Code']);

            if ($verify) {
                $session->set('tfaConfirmed', 1);
                $this->redirectToRoute('login_check');
            } else {
                $dataArray['error'] = "Failed to verify your TFA Code";
            }
        }
        $dataArray['form'] = $tfaForm->createView();

        return $this->render('security/tfa.html.twig' , $dataArray);

    }

}
