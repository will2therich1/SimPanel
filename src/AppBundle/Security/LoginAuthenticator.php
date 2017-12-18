<?php
/**
 * Created by PhpStorm.
 * User: will
 * Date: 08/12/17
 * Time: 11:12
 */
namespace AppBundle\Security;

use AppBundle\Service\EncryptionService;
use AppBundle\Service\GoogleAuthenticatorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;


class LoginAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser(). Returning null will cause this authenticator
     * to be skipped.
     */
    public function getCredentials(Request $request)
    {
        if (isset($_POST['username'])) {
            error_log("Username: " . $_POST['username']);
            $username = $_POST['username'];

            return array(
                'username' => $username
            );
        }


        return null;
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return UserInterface|void
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['username'];

        if (null === $username) {
            return;
        }

        // if a User object, checkCredentials() is called

        return $userProvider->loadUserByUsername($username);
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {

        $password = $user->getPassword();
        $postedPassword = $_POST['password'];
        if (password_verify($postedPassword , $password)){
            if ($user->getTfaStatus() == 1){
                $tfaPosted = $_POST['tfa-code'];
                $tfaService = new GoogleAuthenticatorService();


                $tfaCheck = $tfaService->verifyCode($user->getTfaSecret() , $tfaPosted);
                if ($tfaCheck){
                    return true;
                }else{
                    return false;
                }


            }else {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return RedirectResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'error' => strtr($exception->getMessageKey(), $exception->getMessageData())
        );

        return new RedirectResponse('login?&login=failed' , '302') ;
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            // you might translate this message
            'message' => 'Authentication Required'
        );

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }

}