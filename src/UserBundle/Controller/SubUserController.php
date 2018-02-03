<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\SettingService;
use AppBundle\Service\EncryptionService;
use AppBundle\Service\NetworkServerService;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use UserBundle\Service\PermissionsService;

class SubUserController extends Controller
{
    /**
     * @Route("/user/subusers" , name="SubUserList")
     */
    public function subUserListPage(Request $request)
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $em = $this->getDoctrine()->getManager();


        if($user->getSubUser() === 1)
        {
            return new RedirectResponse('/user');
        }

        $settingService = new SettingService($em);

        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('u')
            ->from('AppBundle:User', 'u')
            ->where('u.subUser = 1')
            ->andWhere('u.subUserFor = :id')
            ->setParameter('id', $userId);

        $result = $queryBuilder->getQuery()->execute();

        $data = [];
        $data['user'] = $user->getUserInfo();
        $data['active'] = "SubUsers";
        $data['subUsers'] = $result;
        $data['site'] = $settingService->getSiteInformation();

        // render the template
        return $this->render('userBundle/subUsers/user.sub.users.html.twig' , $data);

    }
}
