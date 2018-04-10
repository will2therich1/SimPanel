<?php
/**
 * Created by PhpStorm.
 * User: will
 * Date: 10/04/18
 * Time: 10:37
 */

namespace App\Service\Security;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TfaListener
{

    /**
     * @var User $user
     */
    private $user;

    /**
     * @var null|\Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token;
     */
    private $token;

    /**
     * @var ContainerInterface $container
     */
    private $container;

    public function __construct(
      ContainerInterface $container,
      TokenStorageInterface $tokenStorage
    )
    {
        $this->token = $tokenStorage->getToken();
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {


        $request = $event->getRequest();
        $session = $request->getSession();

        if ($request->getRequestUri() == '/login') return true;

        $tfaConfirmed = $session->get('tfaConfirmed');

        if ($tfaConfirmed !== 1 || $tfaConfirmed == null)
        {
            // All in seperate if's as otherwise a redirect issue was caused?
            if (($request->getRequestUri() !== '/tfa')) {
                if ($request->getRequestUri() !== '/logout') {
                    if ($request->getRequestUri() !== '/login_check') {
                        $response = new RedirectResponse('/tfa');
                        $event->setResponse($response);
                    }
                }
            }
        }

    }

    public function onKernelResponse(FilterResponseEvent $event)
    {

    }
}