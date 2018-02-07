<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\SettingService;
use AppBundle\Service\EncryptionService;
use AppBundle\Service\NetworkServerService;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use UserBundle\Service\PermissionsService;
use AppBundle\Entity\User;

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

    /**
     * @Route("/user/subusers/create" , name="createSubUser")
     */
    public function createSubUserAccount(Request $request)
    {
        $data = [];


        $currentUser = $this->getUser();
        $userId = $currentUser->getId();
        $em = $this->getDoctrine()->getManager();


        if($currentUser->getSubUser() === 1)
        {
            return new RedirectResponse('/user');
        }

        $settingService = new SettingService($em);

        if ($request->getMethod() == "POST") {
            $user = new User();

            $userView = 0;
            $userEdit = 0;
            $userManage = 0;

            $user->setFirstName($request->get('userFirstName'));
            $user->setLastName($request->get('userLastName'));
            $user->setEmail($request->get('userEmail'));
            $user->setUsername($request->get('userUsername'));
            $user->setAdmin(0);
            $user->setSubUser(1);
            $user->setStatus(1);
            $user->setTfaStatus(0);
            $user->setSubUserFor($userId);

            if ($request->get('userCanViewRole') == "on")
            {
                $userView = 1;
            }

            if ($request->get('userCanEditRole') == "on")
            {
                $userEdit = 1;
            }
            if ($request->get('userCanManageRole') == "on")
            {
                $userManage = 1;
            }

            $permissionArray = array(
                "USER_VIEW_SERVER" => $userView,
                "USER_EDIT_SERVER" => $userEdit,
                "USER_MANAGE_SERVER" => $userManage
            );

            $user->setSubUserPermissions($permissionArray);

            if ($request->get('userPassword') ==  null)
            {
                $newPassword = $user->generatePassword();

                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            }else{
                if ($request->get('userPassword') === $request->get('userPasswordConfirm'))
                {
                    $newPassword = $request->get('userPasswordConfirm');

                    $hashedPassword = password_hash($newPassword , PASSWORD_DEFAULT);
                }else {

                    $data['error'] = "Provided passwords do not match";
                    $data['user'] = $currentUser->getUserInfo();
                    $data['active'] = "SubUsers";
                    $data['site'] = $settingService->getSiteInformation();

                    // render the template
                    return $this->render('userBundle/subUsers/create.user.sub.users.html.twig', $data);
                }
            }

            $user->setPassword($hashedPassword);

            try {
                $em->persist($user);
                $em->flush();
            } catch (Exception $e)
            {
                $data['error'] = "User creation failed please ensure email & username are unique";
                $data['user'] = $currentUser->getUserInfo();
                $data['active'] = "SubUsers";
                $data['site'] = $settingService->getSiteInformation();

                // render the template
                return $this->render('userBundle/subUsers/create.user.sub.users.html.twig', $data);
            }
            $this->userCreationEmail($user->getEmail() , $user->getUsername() , $newPassword , $request->getHttpHost());

            $data['success'] = "User created and email sent";

        }

        $data['user'] = $currentUser->getUserInfo();
        $data['active'] = "SubUsers";
        $data['site'] = $settingService->getSiteInformation();

        // render the template
        return $this->render('userBundle/subUsers/create.user.sub.users.html.twig' , $data);

    }

    /**
     * @Route("/user/subusers/{id}" , name="viewSubUser")
     */
    public function viewSubUserAccount(Request $request)
    {
        $data = [];


        $currentUser = $this->getUser();
        $userId = $currentUser->getId();
        $em = $this->getDoctrine()->getManager();

        $subUser = $em->getRepository('AppBundle:User')->find($request->get('id'));

        $userEdit = 0;
        $userView = 0;
        $userManage = 0;

        if($currentUser->getSubUser() === 1)
        {
            return new RedirectResponse('/user');
        }

        $settingService = new SettingService($em);

        if ($request->getMethod() == "POST") {

            $subUser->setFirstName($request->get('userFirstName'));
            $subUser->setLastName($request->get('userLastName'));
            $subUser->setEmail($request->get('userEmail'));
            $subUser->setUsername($request->get('userUsername'));

            if ($request->get('userCanViewRole') == "on")
            {
                $userView = 1;
            }

            if ($request->get('userCanEditRole') == "on")
            {
                $userEdit = 1;
            }
            if ($request->get('userCanManageRole') == "on")
            {
                $userManage = 1;
            }

            $permissionArray = array(
                "USER_VIEW_SERVER" => $userView,
                "USER_EDIT_SERVER" => $userEdit,
                "USER_MANAGE_SERVER" => $userManage
            );

            $subUser->setSubUserPermissions($permissionArray);

            $em->persist($subUser);

            try{
                $em->flush();
                $data['success'] = "User updated";

            }catch(\Exception $e)
            {
                $data['error'] = "Failed to update the user please ensure the email is unique";
            }


        }


        $data['subUser'] = $subUser;
        $data['user'] = $currentUser->getUserInfo();
        $data['active'] = "SubUsers";
        $data['site'] = $settingService->getSiteInformation();

        // render the template
        return $this->render('userBundle/subUsers/view.user.sub.users.html.twig' , $data);

    }

    /**
     * @Route("/user/subusers/{id}/delete" , name="deleteSubUser")
     */
    public function deleteSubUserAccount(Request $request)
    {
        $data = [];


        $currentUser = $this->getUser();
        $userId = $currentUser->getId();
        $em = $this->getDoctrine()->getManager();
        $settingService = new SettingService($em);

        $subUser = $em->getRepository('AppBundle:User')->find($request->get('id'));

        if ($request->getMethod() === "POST") {
            if ($subUser->getSubUserFor() == $userId) {
                $em->remove($subUser);
                $em->flush();
            }
        }

        $data['subUser'] = $subUser;
        $data['user'] = $currentUser->getUserInfo();
        $data['active'] = "SubUsers";
        $data['site'] = $settingService->getSiteInformation();

        // render the template
        return $this->render('userBundle/subUsers/view.user.sub.users.html.twig' , $data);

    }

    public function userCreationEmail(string $email, string $username, string $password , $url)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Account Created at SimPanel')
            ->setFrom('no-reply@servers4all.co.uk')
            ->setTo($email)
            ->setBody(
                $this->renderView(
                    'emails/user/user.creation.email.html.twig',
                    array(
                        'username' => $username,
                        'password' => $password,
                        'loginurl' => $url
                    )
                ), 'text/html'
            );
        $this->get('mailer')->send($message);

    }

}
