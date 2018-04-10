<?php
/**
 * TFA Listener service.
 *
 * @author William Rich
 * @copyright https://servers4all.documize.com/s/Wm5Pm0A1QQABQ1xw/simpanel/d/WnDQ5EA1QQABQ154/simpanel-license
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
     * @var User
     */
    private $user;

    /**
     * @var null|\Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
     */
    private $token;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
      ContainerInterface $container,
      TokenStorageInterface $tokenStorage
    ) {
        $this->token = $tokenStorage->getToken();
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $session = $request->getSession();

        if ('/login' == $request->getRequestUri()) {
            return true;
        }

        $tfaConfirmed = $session->get('tfaConfirmed');

        if (1 !== $tfaConfirmed || null == $tfaConfirmed) {
            // All in seperate if's as otherwise a redirect issue was caused?
            if (('/tfa' !== $request->getRequestUri())) {
                if ('/logout' !== $request->getRequestUri()) {
                    if ('/login_check' !== $request->getRequestUri()) {
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
