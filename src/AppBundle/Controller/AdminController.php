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
        $data['currentUser'] = $this->getUserInfo();
        $data['active'] = "Dash";
        $data['block']['user'] = $this->getUserCount();
        $data['block']['admin'] = $this->getAdminCount();

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
        $data['currentUser'] = $this->getUserInfo();
        $data['users'] = $result;
        $data['pages'] = $this->createPagination('/admin/users' , $offset , $limit);
        $data['active'] = "User";


        // replace this example code with whatever you need
        return $this->render('admin/users.admin.html.twig' , $data);
    }

    /**
     * @Route("/admin/users/create", name="CreateUser")
     */
    public function createUsersPage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();


        // Create our Data Array
        $data = [];
        $data['currentUser'] = $this->getUserInfo();
        $data['active'] = 'User';
        $data['success'] = '';
        $data['error'] = '';


        if (isset($_POST['newUserUsername']))
        {
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $username = $_POST['newUserUsername'];
            $email = $_POST['email'];

            $password = $_POST['newUserPassword'];
            $passwordConfirmation = $_POST['newUserPassword-2'];




            if ($password !== ''){

                if ($password == $passwordConfirmation){
                    $setPassword = password_hash($password , PASSWORD_DEFAULT);
                }else{
                    $data['error'] = "Provided passwords do not match";
                }

            }else{

                $passwordGen = $this->generatePassword();
                $setPassword = password_hash($passwordGen , PASSWORD_DEFAULT);

            }

            if ($data['error'] == '') {
                $user = new User();

                $user->setFirstName($firstName);
                $user->setLastName($lastName);
                $user->setEmail($email);
                $user->setPassword($setPassword);
                $user->setAdmin(0);
                $user->setStatus(1);
                $user->setUsername($username);

                try {
                    $em->persist($user);
                    $em->flush();
                } catch (\Exception $e) {
                    $data['error'] = "An unknown error occoured, please ensure the username and email are unique";
                    return $this->render('admin/create.user.admin.html.twig' , $data);

                }

                $data['success'] = "User Created";

            }




            }else{

        }




        // replace this example code with whatever you need
        return $this->render('admin/create.user.admin.html.twig' , $data);
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
        $data['currentUser'] = $this->getUserInfo();
        $data['users'] = $result;
        $data['pages'] = $this->createPagination('/admin/admins' , $offset , $limit);
        $data['active'] = "Admin";


        // replace this example code with whatever you need
        return $this->render('admin/admins.admin.html.twig' , $data);
    }

    /**
     * @Route("/admin/admins/create", name="CreateAdmin")
     */
    public function createAdminsPage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();


        // Create our Data Array
        $data = [];
        $data['currentUser'] = $this->getUserInfo();
        $data['active'] = 'Admin';
        $data['success'] = '';
        $data['error'] = '';


        if (isset($_POST['newUserUsername']))
        {
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $username = $_POST['newUserUsername'];
            $email = $_POST['email'];

            $password = $_POST['newUserPassword'];
            $passwordConfirmation = $_POST['newUserPassword-2'];




            if ($password !== ''){

                if ($password == $passwordConfirmation){
                    $setPassword = password_hash($password , PASSWORD_DEFAULT);
                }else{
                    $data['error'] = "Provided passwords do not match";
                }

            }else{

                $passwordGen = $this->generatePassword();
                $setPassword = password_hash($passwordGen , PASSWORD_DEFAULT);

            }

            if ($data['error'] == '') {
                $user = new User();

                $user->setFirstName($firstName);
                $user->setLastName($lastName);
                $user->setEmail($email);
                $user->setPassword($setPassword);
                $user->setAdmin(1);
                $user->setStatus(1);
                $user->setUsername($username);

                try {
                    $em->persist($user);
                    $em->flush();
                } catch (\Exception $e) {
                    $data['error'] = "An unknown error occoured, please ensure the username and email are unique";
                    return $this->render('admin/create.user.admin.html.twig' , $data);

                }

                $data['success'] = "Admin Created";

            }




        }else{

        }




        // replace this example code with whatever you need
        return $this->render('admin/create.admin.admin.html.twig' , $data);
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
     * Gets the user info and returns this in an array
     *
     * @return array
     *          Array of user information
     */
    public function getUserInfo(){
        // Get the user entity
        $user = $this->getUser();

        // Generate the user information
        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();
        $fullName = $firstName . " " . $lastName;

        $id = $user->getId();
        $username = $user->getUsername();
        $email = $user->getEmail();

        // Prepare data
        $data = [];
        $data['id'] = $id;
        $data['name'] = $fullName;
        $data['username'] = $username;
        $data['email'] = $email;
        $data['firstName'] = $firstName;
        $data['lastName'] = $lastName;


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


}
