<?php
/**
 * The core network server symfony controller.
 *
 * @author William Rich
 * @copyright https://servers4all.documize.com/s/Wm5Pm0A1QQABQ1xw/simpanel/d/WnDQ5EA1QQABQ154/simpanel-license
 */

namespace App\Controller\Admin\Network;

use App\Service\Core\DataCompiler;
use App\Service\Network\NetworkServerService;
use App\Service\Security\EncryptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class AdminNetworkServerController extends Controller
{

    /**
     * @var EncryptionService $encryptionService
     */
    private $encryptionService;

    /**
     * @var DataCompiler
     */
    private $dataCompiler;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * AdminNetworkServerController constructor.
     *
     * @param EncryptionService $encryptionService
     * @param DataCompiler $dataCompiler
     */
    public function __construct(EncryptionService $encryptionService , DataCompiler $dataCompiler , EntityManagerInterface $em)
    {
        $this->encryptionService = $encryptionService;
        $this->dataCompiler = $dataCompiler;
        $this->em = $em;
    }

    public function createNetworkServer(Request $request, NetworkServerService $networkServerService)
    {
        $dataArray = $this->dataCompiler->createDataArray('Network');

        $form = $this->createFormBuilder()
            ->add('server_name', TextType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'Server Name',
                ),
                'label' => 'Server Name',
                'required' => true,
            ))
            ->add('server_ip', TextType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'Server Ip',
                ),
                'label' => 'Server IP',
                'required' => true,
            ))
            ->add('server_port', TextType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'Server FTP Port',
                ),
                'label' => 'Server FTP Port',
                'required' => true,
            ))
            ->add('login_user', TextType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'spd',
                ),
                'label' => 'Server Login User',
                'required' => true,
            ))
            ->add('server_ssh_private_key', TextareaType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'Paste in your ssh key here',
                ),
                'label' => 'SSH Private Key',
                'required' => true,
            ))
            ->add('server_ssh_private_key_password', PasswordType::class , array(
                'attr' => array(
                    'class' => 'form-control form-control-success',
                    'id' => 'inputHorizontalSuccess',
                    'placeholder' => 'SSH Private key password if set!',
                ),
                'label' => 'SSH Key password',
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
            $serverData['server name'] = $formData['server_name'];
            $serverData['server ip'] = $formData['server_ip'];
            $serverData['server ftp port'] = $formData['server_port'];
            $serverData['server login user'] = $formData['login_user'];
            $serverData['server ssh key'] = $formData['server_ssh_private_key'];
            $serverData['server ssh key password'] = $formData['server_ssh_private_key_password'];


            try {
                $server = $networkServerService->createNewNetworkServer($serverData);
                $this->em->persist($server);
                $this->em->flush();
                $dataArray['success'] = "Server created!";
            } catch (\Exception $e) {
                $dataArray['error'] = "An Error occoured creating the admin. the message is as follows: " . $e->getMessage();
            }

        }


        $dataArray['form'] = $form->createView();
        return $this->render('admin_network_server/create.network.server.html.twig' , $dataArray);

    }

    public function testNetworkServerConnectionAPI(Request $request, NetworkServerService $networkServerService, $id)
    {
        try {
            $networkServerService->connectionTest($id);
            if ($networkServerService) {
                $returnData = array(
                    'connected' => true,
                );
            }
        } catch (\Exception $e) {
            $returnData = array(
                'connected' => false,
            );
        }

        $returnJson = json_encode($returnData);

        return new JsonResponse($returnJson);

    }

}
