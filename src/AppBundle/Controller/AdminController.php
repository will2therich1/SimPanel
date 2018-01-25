<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;
use AppBundle\Entity\Settings;
use AppBundle\Service\SettingService;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="adminHomepage")
     */
    public function adminIndexAction(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        if($request->getHttpHost() == "localhost:8000")
        {
            $status = "Development";
        }else{
            $status = "Production";
        }


        // Prepare data for page
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['branding'] = $settingService->getSiteInformation();
        $data['active'] = "Dash";
        $data['block']['user'] = $this->getUserCount();
        $data['block']['admin'] = $this->getAdminCount();
        $data['block']['network'] = $this->getNetworkServerCount();
        $data['block']['status'] = $status;

        // replace this example code with whatever you need
        return $this->render('admin/admin.index.html.twig', $data);
    }


    /**
     * @Route("/admin/users", name="UserAdmin")
     */
    public function adminUsersPage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        $queryBuilder = $em->createQueryBuilder();

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


        // Create the queries!
        if (isset($get['name']) && $get['name'] !== '') {

            $queryBuilder->select('u')
                ->from('AppBundle:User', 'u')
                ->where('u.username LIKE :name')
                ->andWhere('u.admin = 0')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->setParameter('name', '%' . $get['name'] . '%');


            $result = $queryBuilder->getQuery()->execute();


        } elseif (isset($get['id']) && $get['id'] !== '') {

            $queryBuilder->select('u')
                ->from('AppBundle:User', 'u')
                ->where('u.id = :id')
                ->andWhere('u.admin = 0')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->setParameter('id', $get['id']);

            $result = $queryBuilder->getQuery()->execute();

        }


        if (!isset($result)) {
            $queryBuilder->select('u')
                ->from('AppBundle:User', 'u')
                ->where('u.admin = 0')
                ->setMaxResults($limit)
                ->setFirstResult($offset);

            $result = $queryBuilder->getQuery()->execute();
        }


        // Create our Data Array
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['branding'] = $settingService->getSiteInformation();
        $data['users'] = $result;
        $data['pages'] = $this->createPagination('/admin/users', $offset, $limit);
        $data['active'] = "User";


        // replace this example code with whatever you need
        return $this->render('admin/users/users.admin.html.twig', $data);
    }


    /**
     * @Route("/admin/admins", name="AdminList")
     */
    public function adminsListPage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        $queryBuilder = $em->createQueryBuilder();

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


        // Create the queries!
        if (isset($get['name']) && $get['name'] !== '') {

            $queryBuilder->select('u')
                ->from('AppBundle:User', 'u')
                ->where('u.username LIKE :name')
                ->andWhere('u.admin = 1')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->setParameter('name', '%' . $get['name'] . '%');


            $result = $queryBuilder->getQuery()->execute();


        } elseif (isset($get['id']) && $get['id'] !== '') {

            $queryBuilder->select('u')
                ->from('AppBundle:User', 'u')
                ->where('u.id = :id')
                ->andWhere('u.admin = 1')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->setParameter('id', $get['id']);

            $result = $queryBuilder->getQuery()->execute();

        }


        if (!isset($result)) {
            $queryBuilder->select('u')
                ->from('AppBundle:User', 'u')
                ->where('u.admin = 1')
                ->setMaxResults($limit)
                ->setFirstResult($offset);

            $result = $queryBuilder->getQuery()->execute();
        }


        // Create our Data Array
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['branding'] = $settingService->getSiteInformation();
        $data['users'] = $result;
        $data['pages'] = $this->createPagination('/admin/admins', $offset, $limit);
        $data['active'] = "Admin";


        // replace this example code with whatever you need
        return $this->render('admin/admins.admin.html.twig', $data);
    }

    /**
     * @Route("/admin/network", name="NetworkServerList")
     */
    public function adminNetworkServerListPage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        $data = [];
        $data['branding'] = $settingService->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';

        $queryBuilder = $em->createQueryBuilder();


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


        // Create the queries!
        if (isset($get['name']) && $get['name'] !== '') {

            $queryBuilder->select('u')
                ->from('AppBundle:NetworkServer', 'ns')
                ->where('ns.name = :name')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->setParameter('name', $get['name']);


            $result = $queryBuilder->getQuery()->execute();


        } elseif (isset($get['id']) && $get['id'] !== '') {

            $queryBuilder->select('ns')
                ->from('AppBundle:NetworkServer', 'ns')
                ->where('ns.id = :id')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->setParameter('id', $get['id']);

            $result = $queryBuilder->getQuery()->execute();

        }


        if (!isset($result)) {
            $queryBuilder->select('ns')
                ->from('AppBundle:NetworkServer', 'ns')
                ->setMaxResults($limit)
                ->setFirstResult($offset);

            $result = $queryBuilder->getQuery()->execute();
        }


        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['pages'] = $this->createPagination('/admin/network', $offset, $limit);
        $data['servers'] = $result;
        $data['active'] = "Network";


        // replace this example code with whatever you need
        return $this->render('admin/network/network.admin.html.twig', $data);
    }

    /**
     * @Route("/admin/servers/templates", name="ServerTemplateList")
     */
    public function adminServerTemplatesListPage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        // Get our setting service
        $settingService = new SettingService($em);

        $data = [];
        $data['branding'] = $settingService->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';

        $queryBuilder = $em->createQueryBuilder();


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


        // Create the queries!
        if (isset($get['name']) && $get['name'] !== '') {


            $queryBuilder->select('st')
                ->from('AppBundle:ServerTemplate', 'st')
                ->where('st.template_name LIKE :name')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->setParameter('name', '%'.$get['name'].'%');


            $result = $queryBuilder->getQuery()->execute();


        } elseif (isset($get['id']) && $get['id'] !== '') {

            $queryBuilder->select('st')
                ->from('AppBundle:ServerTemplate', 'st')
                ->where('st.id = :id')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->setParameter('id', $get['id']);

            $result = $queryBuilder->getQuery()->execute();

        }


        if (!isset($result)) {
            $queryBuilder->select('st')
                ->from('AppBundle:ServerTemplate', 'st')
                ->setMaxResults($limit)
                ->setFirstResult($offset);

            $result = $queryBuilder->getQuery()->execute();
        }


        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['pages'] = $this->createPagination('/admin/servers/templates', $offset, $limit);
        $data['templates'] = $result;
        $data['active'] = "Templates";


        // replace this example code with whatever you need
        return $this->render('admin/templates/server.template.admin.html.twig', $data);
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


    /**
     * Counts the amount of users in the database
     *
     * @return string
     *          Returns a string with the amount of users in the database
     */
    public function getUserCount()
    {
        // Get Doctine
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();


        $userCountQuery = $queryBuilder->select('count(u)')
            ->from('AppBundle:User', 'u')
            ->Where('u.admin = 0');

        $userCountQueryCount = $userCountQuery->getQuery()->getSingleScalarResult();

        return $userCountQueryCount;
    }

    /**
     * Counts the amount of admins in the database
     *
     * @return string
     *          Returns a string with the amount of admins in the database
     */
    public function getAdminCount()
    {
        // Get Doctine
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();


        $userCountQuery = $queryBuilder->select('count(u)')
            ->from('AppBundle:User', 'u')
            ->Where('u.admin = 1');

        $userCountQueryCount = $userCountQuery->getQuery()->getSingleScalarResult();

        return $userCountQueryCount;
    }

    /**
     * Counts the amount of admins in the database
     *
     * @return string
     *          Returns a string with the amount of admins in the database
     */
    public function getNetworkServerCount()
    {
        // Get Doctine
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();


        $networkCountQuery = $queryBuilder->select('count(u)')
            ->from('AppBundle:NetworkServer', 'u');

        $networkCountQueryCount = $networkCountQuery->getQuery()->getSingleScalarResult();

        return $networkCountQueryCount;
    }




}
