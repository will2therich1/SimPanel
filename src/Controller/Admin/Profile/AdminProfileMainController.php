<?php
/**
 * Admin profile controller.
 *
 * @author William Rich
 * @copyright https://servers4all.documize.com/s/Wm5Pm0A1QQABQ1xw/simpanel/d/WnDQ5EA1QQABQ154/simpanel-license
 */

namespace App\Controller\Admin\Profile;

use App\Service\Core\DataCompiler;
use App\Service\User\UserManagementService;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;

class AdminProfileMainController extends Controller
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var DataCompiler
     */
    private $dataCompiler;

    /**
     * AdminProfileMainController constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param DataCompiler           $dataCompiler
     */
    public function __construct(EntityManagerInterface $entityManager, DataCompiler $dataCompiler)
    {
        $this->em = $entityManager;
        $this->dataCompiler = $dataCompiler;
    }

    /**
     * Index page for admin profiles (information  page).
     *
     * @param Request               $request
     * @param UserManagementService $ums
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function profileIndexPage(Request $request, UserManagementService $ums)
    {
        $dataArray = $this->dataCompiler->createDataArray('dash');
        $dataArray['tab'] = 'Info';

        $user = $this->getUser();

        $form = $this->createFormBuilder()
          ->add('user_id', TextType::class, [
            'attr' => [
              'class' => 'form-control form-control-success',
              'id' => 'inputHorizontalSuccess',
              'placeholder' => 'User ID',
              'readonly' => true,
              'value' => $user->getId(),
            ],
            'label' => 'User ID',
            'required' => true,
          ])
          ->add('user_username', TextType::class, [
            'attr' => [
              'class' => 'form-control form-control-success',
              'id' => 'inputHorizontalSuccess',
              'placeholder' => 'Username',
              'value' => $user->getUsername(),
            ],
            'label' => 'Username',
            'required' => true,
          ])
          ->add('user_email', EmailType::class, [
            'attr' => [
              'class' => 'form-control form-control-success',
              'id' => 'inputHorizontalSuccess',
              'placeholder' => 'Email@email',
              'value' => $user->getEmail(),
            ],
            'label' => 'User Email',
            'required' => true,
          ])
          ->add('first_name', TextType::class, [
            'attr' => [
              'class' => 'form-control form-control-success',
              'id' => 'inputHorizontalSuccess',
              'placeholder' => 'First Name',
              'value' => $user->getFirstName(),
            ],
            'label' => 'First Name',
            'required' => true,
          ])
          ->add('last_name', TextType::class, [
            'attr' => [
              'class' => 'form-control form-control-success',
              'id' => 'inputHorizontalSuccess',
              'placeholder' => 'Last Name',
              'value' => $user->getLastName(),
            ],
            'label' => 'Last Name',
            'required' => true,
          ])
          ->add('Update', SubmitType::class, [
            'attr' => [
              'class' => 'btn btn-primary',
              'style' => 'margin: 10px;',
            ],
          ])
          ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dataArray['error'] = '';

            $formData = $form->getData();
            $user->setFirstName($formData['first_name']);
            $user->setLastName($formData['last_name']);

            try {
                if ($ums->checkIfEmailAndUsernameAreUnique($formData['user_email'], $user->getId())) {
                    $user->setEmail($formData['user_email']);
                } else {
                    $dataArray['error'] .= 'ERROR Email not unique';
                }

                if ($ums->checkIfEmailAndUsernameAreUnique($formData['user_username'], $user->getId())) {
                    $user->setUsername($formData['user_username']);
                } else {
                    $dataArray['error'] .= 'ERROR Username not unique';
                }

                $this->em->persist($user);
                $this->em->flush();
                $dataArray['success'] = 'Account updated, changes may not be visable until refresh';
            } catch (\Exception $e) {
                $dataArray['error'] .= 'Error occoured with message: '.$e->getMessage();
            }
        }

        $dataArray['form'] = $form->createView();

        return $this->render('admin_profile_main/information.profile.admin.html.twig', $dataArray);
    }

    /**
     * Password Changing page for admin profiles (information  page).
     *
     * @param Request               $request
     * @param UserManagementService $ums
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function profilePasswordPage(Request $request, UserManagementService $ums)
    {
        $dataArray = $this->dataCompiler->createDataArray('dash');
        $dataArray['tab'] = 'ChangePass';

        $user = $this->getUser();

        $form = $this->createFormBuilder()
          ->add('current_password', PasswordType::class, [
            'attr' => [
              'class' => 'form-control form-control-success',
              'id' => 'inputHorizontalSuccess',
            ],
            'label' => 'Current Password',
            'required' => true,
          ])
          ->add('new_password', PasswordType::class, [
            'attr' => [
              'class' => 'form-control form-control-success',
              'id' => 'inputHorizontalSuccess fmpass',
            ],
            'label' => 'New Password',
            'required' => true,
          ])
          ->add('new_password_confirm', PasswordType::class, [
            'attr' => [
              'class' => 'form-control form-control-success',
              'id' => 'inputHorizontalSuccess',
            ],
            'label' => 'Confirm Password',
            'required' => true,
          ])
          ->add('Update', SubmitType::class, [
            'attr' => [
              'class' => 'btn btn-primary',
              'style' => 'margin: 10px;',
            ],
          ])
          ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user = $ums->updateUserPassword($form->getData(), $user);
                $this->em->persist($user);
                $this->em->flush();
                $dataArray['success'] = 'password updated';
            } catch (\Exception $e) {
                $dataArray['error'] = 'An error occoured with message'.$e->getMessage();
            }
        }

        $dataArray['form'] = $form->createView();

        return $this->render('admin_profile_main/password.profile.admin.html.twig', $dataArray);
    }
}
