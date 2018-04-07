<?php

namespace App\Controller\Admin\Core;

use App\Service\User\UserManagementService;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Service\Core\DataCompiler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdminAdminController extends Controller
{
    /**
     * @var DataCompiler|null
     */
    private $dataCompiler = null;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(DataCompiler $dataCompiler , EntityManagerInterface $em)
    {
        $this->dataCompiler = $dataCompiler;
        $this->em = $em;
    }


    public function adminViewPage($id, Request $request, UserManagementService $usm)
    {
        $userToView = $this->em->getRepository('App:User')->find($id);
        $dataArray = $this->dataCompiler->createDataArray('User');
        $dataArray['error'] = '';

        $error = false;

        if ( $id == '')
        {
           return false;
        }

        $form = $this->createFormBuilder()
            ->add('id', TextType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'ID',
                    'readonly' => true,
                    'value' => $userToView->getId(),
                ),
                'label' => 'ID',
                'required' => true,
            ))
            ->add('first_name', TextType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'First Name',
                    'value' => $userToView->getFirstName(),
                ),
                'label' => 'First Name',
                'required' => true,
            ))
            ->add('last_name', TextType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'Last Name',
                    'value' => $userToView->getLastName(),
                ),
                'label' => 'Last Name',
                'required' => true,
            ))
            ->add('username', TextType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'Username',
                    'value' => $userToView->getUsername(),
                ),
                'label' => 'Username',
                'required' => true,
            ))
            ->add('email', TextType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'Email',
                    'value' => $userToView->getEmail(),
                ),
                'label' => 'Email',
                'required' => true,
            ))
            ->add('Update', SubmitType::class , array(
                'attr' => array(
                    'class' => 'btn btn-primary',
                    'style' => 'margin: 10px;',
                ),
            ))
            ->getForm();

        $form->handleRequest($request);

        // Form Submit handler!
        if ($form->isSubmitted() && $form->isValid())
        {
            $formData = $form->getData();

            // Update the user!
            $userToView->setFirstName($formData['first_name']);
            $userToView->setLastName($formData['last_name']);

            // Ensure usernames and emails are unique
            try {
                if ($usm->checkIfEmailAndUsernameAreUnique($formData['username'] , $id)) {
                    $userToView->setUsername($formData['username']);
                } else {
                    $dataArray['error'] .= 'ERROR: Username is not unique not being updated, ';
                }
                if ($usm->checkIfEmailAndUsernameAreUnique($formData['email'] , $id)) {
                    $userToView->setEmail($formData['email']);
                } else {
                    $dataArray['error'] .= 'ERROR: Email is not unique not being updated,';
                }
            } catch (\Exception $e) {
                $dataArray['error'] .= "ERROR: An error occoured checking that the username & emails are unique. Error message:" . $e->getMessage() . $e->getFile() . $e->getLine() . $e->getCode() . '/n';
            }

            try {
                $this->em->persist($userToView);
                $this->em->flush();

                // Only do this if no errors have been reported
                if (!$error) {
                    $dataArray['success'] = "Admin has been updated,If you don't see the changes you may need to refresh.";
                }
            } catch (\Exception $e) {
                $dataArray['error'] = "An Error occoured with the message: " . $e->getMessage();
            }
        }

        $dataArray['form'] = $form->createView();
        $dataArray['user'] = $userToView;

        return $this->render(':admin_admin:view.admin.html.twig' , $dataArray);
    }

    public function adminCreatePage(Request $request , UserManagementService $usm)
    {
        $dataArray = $this->dataCompiler->createDataArray('User');

        $form = $this->createFormBuilder()
            ->add('first_name', TextType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'First Name',
                ),
                'label' => 'First Name',
                'required' => true,
            ))
            ->add('last_name', TextType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'Last Name',
                ),
                'label' => 'Last Name',
                'required' => true,
            ))
            ->add('username', TextType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'Username',
                ),
                'label' => 'Username',
                'required' => true,
            ))
            ->add('email', TextType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'Email',
                ),
                'label' => 'Email',
                'required' => true,
            ))
            ->add('password', PasswordType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'Password',
                ),
                'label' => 'Password, if not filled in a password will be generated.',
                'required' => false,
            ))
            ->add('password_confirm', PasswordType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'confirmPassword',
                ),
                'label' => 'Confirm password.',
                'required' => false,
            ))
            ->add('Create', SubmitType::class , array(
                'attr' => array(
                    'class' => 'btn btn-primary',
                    'style' => 'margin: 10px;',
                ),
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $formData = $form->getData();

            $userData['first name'] = $formData['first_name'];
            $userData['last name'] = $formData['last_name'];
            $userData['username'] = $formData['username'];
            $userData['email'] = $formData['email'];
            $userData['admin'] = 1;
            $userData['password'] = $formData['password'];
            $userData['password_confirm'] = $formData['password_confirm'];
            $userData['subuser'] = 0;

            try {
                $user = $usm->createUser($userData);
                $this->em->persist($user);
                $this->em->flush();
                $dataArray['success'] = "Admin created!";
            } catch (\Exception $e) {
                $dataArray['error'] = "An Error occoured creating the admin. the message is as follows: " . $e->getMessage();
            }

        }


        $dataArray['form'] = $form->createView();

        return $this->render('admin_admin/create.admin.html.twig' , $dataArray);
    }
}
