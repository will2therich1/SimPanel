<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdminAdminController extends Controller
{
    /**
     * @Route("/admin/admins/create", name="CreateAdmin")
     */
    public function createAdminsPage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();


        // Create our Data Array
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
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

                $password = $this->generatePassword();
                $setPassword = password_hash($password , PASSWORD_DEFAULT);

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
                $this->userCreationEmail($email , $username , $password);

            }




        }else{

        }




        // replace this example code with whatever you need
        return $this->render('admin/create.admin.admin.html.twig' , $data);
    }
    /**
     * @Route("/admin/admins/{id}", name="ViewAdmin")
     */
    public function adminViewAdminPage(Request $request)
    {
        // Set our data stuff
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = "Admin";
        $data['success'] = '';
        $data['error'] = '';


        // Get Doctrine
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->find($request->attributes->get('id'));
        $userArray = array($user);

        $data['user'] = $userArray['0'];


        if (isset($_POST['id']) && $_POST['id'] !== '') {
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $username = $_POST['newUsername'];
            $email = $_POST['email'];


            try {

                $user->setFirstName($firstName);
                $user->setLastName($lastName);
                $user->setEmail($email);
                $user->setUsername($username);
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                $data['success'] = "Admin Updated";

            } catch (PDOException $e) {
                $data['error'] = "User Failed to Update";
                return $this->render('admin/users/view.user.admin.html.twig', $data);

            }
        }
        // replace this example code with whatever you need
        return $this->render('admin/users/view.user.admin.html.twig' , $data);
    }

    /**
     * @Route("/admin/admins/{id}/deactivate", name="DeactivateAdmin")
     */
    public function adminAdminDeactivatePage(Request $request)
    {
        // Set our data stuff
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = "Admin";
        $data['success'] = '';
        $data['error'] = '';


        // Get Doctrine
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->find($request->attributes->get('id'));
        $user->setStatus(0);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();


        // replace this example code with whatever you need
        return new RedirectResponse('/admin/admins');
    }

    /**
     * @Route("/admin/admins/{id}/activate", name="ActivateAdmin")
     */
    public function adminAdminActivatePage(Request $request)
    {
        // Set our data stuff
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = "Admin";
        $data['success'] = '';
        $data['error'] = '';


        // Get Doctrine
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->find($request->attributes->get('id'));
        $user->setStatus(1);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();


        $data['success'] = 'User Deactivated';


        // replace this example code with whatever you need
        return new RedirectResponse('/admin/admins');
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

    public function userCreationEmail(string $email , string $username , string $password)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Account Created at SimPanel')
            ->setFrom('no-reply@servers4all.co.uk')
            ->setTo($email)
            ->setBody(
                $this->renderView(
                    'emails/user/user.creation.email.html.twig',
                    array('username' => $username,
                        'password' => $password,
                        'loginurl' => 'https://poisonpanel.servers4all.co.uk')
                ), 'text/html'
            )
        ;
        $this->get('mailer')->send($message);

    }

}
