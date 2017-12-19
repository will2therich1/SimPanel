<?php

namespace AppBundle\Controller;

use AppBundle\Service\EncryptionService;
use Doctrine\DBAL\Driver\PDOException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;

class AdminUserController extends Controller
{

    /**
     * @Route("/admin/users/create", name="CreateUser")
     */
    public function createUsersPage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();


        // Create our Data Array
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
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
                $user->setTfaStatus(0);

                try {
                    $em->persist($user);
                    $em->flush();
                } catch (\Exception $e) {
                    $data['error'] = "An unknown error occoured, please ensure the username and email are unique";
                    return $this->render('admin/users/create.user.admin.html.twig' , $data);

                }

                $data['success'] = "User Created";

            }




        }else{

        }




        // replace this example code with whatever you need
        return $this->render('admin/users/create.user.admin.html.twig' , $data);
    }

    /**
     * @Route("/admin/users/{id}", name="ViewUser")
     */
    public function adminViewUserPage(Request $request)
    {
        // Set our data stuff
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = "User";
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
                $data['success'] = "User Updated";

            } catch (PDOException $e) {
                $data['error'] = "User Failed to Update";
                return $this->render('admin/users/view.user.admin.html.twig', $data);

            }
        }
        // replace this example code with whatever you need
        return $this->render('admin/users/view.user.admin.html.twig' , $data);
    }

    /**
     * @Route("/admin/users/{id}/deactivate", name="DeactivateUser")
     */
    public function adminUserDeactivatePage(Request $request)
    {
        // Set our data stuff
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = "User";
        $data['success'] = '';
        $data['error'] = '';


        // Get Doctrine
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->find($request->attributes->get('id'));
        $user->setStatus(0);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();


        // replace this example code with whatever you need
        return new RedirectResponse('/admin/users');
    }

    /**
     * @Route("/admin/users/{id}/activate", name="ActivateUser")
     */
    public function adminUserActivatePage(Request $request)
    {
        // Set our data stuff
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = "User";
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
        return new RedirectResponse('/admin/users');
    }
}
