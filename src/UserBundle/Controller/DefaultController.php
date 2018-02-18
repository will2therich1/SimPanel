<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\ServerTemplate;
use AppBundle\Service\SettingService;
use UserBundle\Service\PermissionsService;

class DefaultController extends Controller
{
    /**
     * @Route("/user", name="userIndex")
     */
    public function userIndexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);

        $user = $this->getUser();
        $userId = $user->getId();


        if($user->getSubUser() === 1)
        {
            $permissionsService = new PermissionsService($user, $em);

            $permission = $permissionsService->checkPermission("USER_VIEW_SERVER");
            $owner = $permissionsService->getSubUserOwner();

            if ($permission) {
                $userId = $owner->getId();
            }
        }


        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('gs')
            ->from('ServerBundle:GameServer', 'gs')
            ->where('gs.ownerId LIKE :id')
            ->setMaxResults(10)
            ->setParameter('id', $userId);

        $result = $queryBuilder->getQuery()->execute();

        $data = [];
        $data['active'] = "Dash";
        $data['user'] = $user->getUserInfo();
        $data['servers'] = $result;
        $data['site'] = $settingService->getSiteInformation();

        // replace this example code with whatever you need
        return $this->render('userBundle/dashboardpage.html.twig' , $data);
    }




}
