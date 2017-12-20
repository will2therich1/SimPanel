<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;


class AdminController extends Controller
{
    /**
     * @Route("/admin", name="adminHomepage")
     */
    public function adminIndexAction()
    {

        // Prepare data for page
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['branding'] = $this->getSiteInformation();
        $data['active'] = "Dash";
        $data['block']['user'] = $this->getUserCount();
        $data['block']['admin'] = $this->getAdminCount();
        $data['block']['network'] = $this->getNetworkServerCount();

        // replace this example code with whatever you need
        return $this->render('admin/admin.index.html.twig' , $data);
    }


    /**
     * @Route("/admin/users", name="UserAdmin")
     */
    public function adminUsersPage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();

        $get = $_GET;
        // Set the offset for queries
        if (isset($get['offset']) && $get['offset'] !== ''){
            $offset = $get['offset'];
        }else{
            $offset = 0;
        }

        // Set the limit for queries
        if (isset($get['limit']) && $get['limit'] !== ''){
            $limit = $get['limit'];
        }else{
            $limit = 10;
        }


        // Create the queries!
        if (isset($get['name']) && $get['name'] !== ''){

                $queryBuilder->select('u')
                    ->from('AppBundle:User', 'u')
                    ->where('u.username LIKE :name')
                    ->andWhere('u.admin = 0')
                    ->setMaxResults($limit)
                    ->setFirstResult($offset)
                    ->setParameter('name', '%'.$get['name'].'%');


                $result = $queryBuilder->getQuery()->execute();


        }elseif(isset($get['id']) && $get['id'] !== ''){

                $queryBuilder->select('u')
                    ->from('AppBundle:User', 'u')
                    ->where('u.id = :id')
                    ->andWhere('u.admin = 0')
                    ->setMaxResults($limit)
                    ->setFirstResult($offset)
                    ->setParameter('id', $get['id']);

                $result = $queryBuilder->getQuery()->execute();

        }


        if (!isset($result)){
            $queryBuilder->select('u')
                ->from('AppBundle:User' , 'u')
                ->where('u.admin = 0')
                ->setMaxResults($limit)
                ->setFirstResult($offset);

            $result = $queryBuilder->getQuery()->execute();
        }




        // Create our Data Array
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['branding'] = $this->getSiteInformation();
        $data['users'] = $result;
        $data['pages'] = $this->createPagination('/admin/users' , $offset , $limit);
        $data['active'] = "User";


        // replace this example code with whatever you need
        return $this->render('admin/users/users.admin.html.twig' , $data);
    }


    /**
     * @Route("/admin/admins", name="AdminList")
     */
    public function adminsListPage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();

        $get = $_GET;
        // Set the offset for queries
        if (isset($get['offset']) && $get['offset'] !== ''){
            $offset = $get['offset'];
        }else{
            $offset = 0;
        }

        // Set the limit for queries
        if (isset($get['limit']) && $get['limit'] !== ''){
            $limit = $get['limit'];
        }else{
            $limit = 10;
        }


        // Create the queries!
        if (isset($get['name']) && $get['name'] !== ''){

            $queryBuilder->select('u')
                ->from('AppBundle:User', 'u')
                ->where('u.username LIKE :name')
                ->andWhere('u.admin = 1')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->setParameter('name', '%'.$get['name'].'%');


            $result = $queryBuilder->getQuery()->execute();


        }elseif(isset($get['id']) && $get['id'] !== ''){

            $queryBuilder->select('u')
                ->from('AppBundle:User', 'u')
                ->where('u.id = :id')
                ->andWhere('u.admin = 1')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->setParameter('id', $get['id']);

            $result = $queryBuilder->getQuery()->execute();

        }


        if (!isset($result)){
            $queryBuilder->select('u')
                ->from('AppBundle:User' , 'u')
                ->where('u.admin = 1')
                ->setMaxResults($limit)
                ->setFirstResult($offset);

            $result = $queryBuilder->getQuery()->execute();
        }




        // Create our Data Array
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['branding'] = $this->getSiteInformation();
        $data['users'] = $result;
        $data['pages'] = $this->createPagination('/admin/admins' , $offset , $limit);
        $data['active'] = "Admin";


        // replace this example code with whatever you need
        return $this->render('admin/admins.admin.html.twig' , $data);
    }

    /**
     * @Route("/admin/network", name="NetworkServerList")
     */
    public function adminNetworkServerListPage()
    {
        $data = [];
        $data['branding'] = $this->getSiteInformation();
        $data['success'] = '';
        $data['error'] = '';
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();


        $get = $_GET;
        // Set the offset for queries
        if (isset($get['offset']) && $get['offset'] !== ''){
            $offset = $get['offset'];
        }else{
            $offset = 0;
        }

        // Set the limit for queries
        if (isset($get['limit']) && $get['limit'] !== ''){
            $limit = $get['limit'];
        }else{
            $limit = 10;
        }


        // Create the queries!
        if (isset($get['name']) && $get['name'] !== ''){

            $queryBuilder->select('u')
                ->from('AppBundle:NetworkServer', 'ns')
                ->where('ns.name = :name')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->setParameter('name', $get['name']);


            $result = $queryBuilder->getQuery()->execute();


        }elseif(isset($get['id']) && $get['id'] !== ''){

            $queryBuilder->select('ns')
                ->from('AppBundle:NetworkServer', 'ns')
                ->where('ns.id = :id')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->setParameter('id', $get['id']);

            $result = $queryBuilder->getQuery()->execute();

        }


        if (!isset($result)){
            $queryBuilder->select('ns')
                ->from('AppBundle:NetworkServer', 'ns')
                ->setMaxResults($limit)
                ->setFirstResult($offset);

            $result = $queryBuilder->getQuery()->execute();
        }


        // Create our Data Array
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['pages'] = $this->createPagination('/admin/network' , $offset , $limit);
        $data['servers'] = $result;
        $data['active'] = "Network";


        // replace this example code with whatever you need
        return $this->render('admin/network/network.admin.html.twig' , $data);
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
    public function createPagination($url , $offset , $limit){
        // Setting the limit
        $nextLimit = $limit + 10;

        if ($limit == 10) {
            $lastLimit = 10;
        }else{
            $lastLimit = $limit - 10;
        }

        // Setting the offset
        $nextOffset = $offset + 10;

        if ($offset == 0) {

            $lastOffset = 0;
        }else{

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
    public function getUserCount(){
        // Get Doctine
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();


        $userCountQuery =  $queryBuilder->select('count(u)')
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
    public function getAdminCount(){
        // Get Doctine
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();


        $userCountQuery =  $queryBuilder->select('count(u)')
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
    public function getNetworkServerCount(){
        // Get Doctine
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();


        $networkCountQuery =  $queryBuilder->select('count(u)')
            ->from('AppBundle:NetworkServer', 'u');

        $networkCountQueryCount = $networkCountQuery->getQuery()->getSingleScalarResult();

        return $networkCountQueryCount;
    }

    /**
     * Generates a random password
     *
     * @param int    $length         Length of the password
     * @param bool   $add_dashes     Add dashes to the password
     * @param string $available_sets Rules to use
     *
     * @return bool|string
     */
    public function generatePassword($length = 9, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        if(strpos($available_sets, 'l') !== false) {
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        }
        if(strpos($available_sets, 'u') !== false) {
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }
        if(strpos($available_sets, 'd') !== false) {
            $sets[] = '23456789';
        }
        if(strpos($available_sets, 's') !== false) {
            $sets[] = '!@#$%&*?';
        }
        $all = '';
        $password = '';
        foreach($sets as $set)
        {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for($i = 0; $i < $length - count($sets); $i++) {
            $password .= $all[array_rand($all)];
        }
        $password = str_shuffle($password);
        if(!$add_dashes) {
            return $password;
        }
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while(strlen($password) > $dash_len)
        {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }

    /**
     * Gets the site information and returns this
     *
     * @return array
     */
    public function getSiteInformation()
    {

        $returnArray = [];
        $returnArray['panelName'] = $this->getSetting('PanelName')->getSettingValue();
        $returnArray['panelNamePart1'] = $this->getSetting('PanelNamePart1')->getSettingValue();
        $returnArray['PanelNamePart2'] = $this->getSetting('PanelNamePart2')->getSettingValue();
        $returnArray['PanelNameShortPart1'] = $this->getSetting('PanelNameShortPart1')->getSettingValue();
        $returnArray['PanelNameShortPart2'] = $this->getSetting('PanelNameShortPart2')->getSettingValue();

        return $returnArray;
    }

    /**
     * Returns the Setting Object!
     *
     * If the setting dosen't exist then it will be created.
     *
     * @param $settingName
     *          Name of the Setting
     * @return Settings|mixed
     */
    public function getSetting($settingName )
    {
        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings');
        $query = $settings->createQueryBuilder('s');
        $result = $query->select('s.id')
            ->where('s.settingName = :setting')
            ->setParameter('setting' , $settingName)
            ->getQuery()
            ->execute();

        if (empty($result))
        {
            $newSetting = new Settings();
            $newSetting->setSettingName($settingName);
            $newSetting->setSettingValue(0);
            $newSetting->setSettingUpdatedTime(new \DateTime());

            $this->getDoctrine()->getManager()->persist($newSetting);
            $this->getDoctrine()->getManager()->flush();

            return $newSetting;
        }

        $result = $result[0];
        $id = $result['id'];
        

        $returnObject = $this->getDoctrine()->getRepository('AppBundle:Settings')->find($id);

        return $returnObject;
    }



}
