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
use ServerBundle\Service\GameServerService;
use UserBundle\Service\ActionsService;

class ActionsController extends Controller
{

    /**
     * @Route("/user/actions" , name="ActionsPage")
     */
    public function getActions()
    {
        $data = [];


        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);
        $actionsService = new ActionsService($em);




        $data['user'] = $user->getUserInfo();
        $data['active'] = "ActionLog";
        $data['subUser'] = $user->getSubUser();
        $data['site'] = $settingService->getSiteInformation();


        if ($user->getSubUser() == 1)
        {
            return $this->render('userBundle/user.403.html.twig' , $data);
        }

        $get = $_GET;

        // Set the offset for queries
        if (isset($get['offset']) && $get['offset'] !== '') {
            $offset = $get['offset'];
        } else {
            $offset = 0;
        }

        // Set the limit for queries
        if (isset($get['limit']) && $get['limit'] !== '') {
            $limit = $get['limit'];
        } else {
            $limit = 10;
        }

        $pagnation = $this->createPagination('actions' , $limit , $offset);
        $data['pagination'] = $pagnation;


        $actions = $actionsService->getRecentActionsForAUser($user->getId() , $offset , $limit);

        $data['actions'] = $actions;

        return $this->render('userBundle/user.actions.html.twig' , $data);

    }
    /**
     * @Route("/user/actions/{id}/delete" , name="ActionsDelete")
     */
    public function deleteActions(Request $request)
    {
        $data = [];


        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);
        $actionsService = new ActionsService($em);
        $id = $request->get('id');



        $data['user'] = $user->getUserInfo();
        $data['active'] = "ActionLog";
        $data['subUser'] = $user->getSubUser();
        $data['site'] = $settingService->getSiteInformation();


        if ($user->getSubUser() == 1)
        {
            return $this->render('userBundle/user.403.html.twig');
        }




        $actions = $actionsService->deleteAAction($id);


        return new RedirectResponse('/user/actions');

    }

    /**
     * @param $url
     *       The url for the pagination to link to.
     * @param $offset
     *       The current Offset
     * @param $limit
     *       The current Limit
     *
     * @return array
     *        Returns array containing the two necessary links
     */
    public function createPagination($url, $offset, $limit)
    {
        // Setting the limit
        $nextLimit = $limit + 10;

        if ($limit == 10) {
            $lastLimit = 10;
        } else {
            $lastLimit = $limit - 10;
        }

        // Setting the offset
        $nextOffset = $offset + 10;

        if ($offset == 0) {

            $lastOffset = 0;
        } else {

            $lastOffset = $offset - 10;
        }

        // Create the links
        $nextPageLink = "$url?limit=" . $nextLimit . "&offset=" . $nextOffset;
        $lastPageLink = "$url?limit=" . $lastLimit . "&offset=" . $lastOffset;

        $data['nextlink'] = $nextPageLink;
        $data['lastlink'] = $lastPageLink;

        return $data;
    }

}
