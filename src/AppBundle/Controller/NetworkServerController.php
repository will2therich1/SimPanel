<?php

namespace AppBundle\Controller;

use AppBundle\Service\EncryptionService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\NetworkServer;

class NetworkServerController extends Controller
{
    /**
     * @Route("/admin/network/create", name="CreateNetworkServer")
     */
    public function createNetworkServerPage()
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();


        // Create our Data Array
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['active'] = 'Network';
        $data['success'] = '';
        $data['error'] = '';


        if (isset($_POST['name']))
        {
            $serverName = $_POST['name'];
            $loginUser = $_POST['login_name'];
            $ftpPort = $_POST['ftp_port'];
            $serverIp = $_POST['ip'];
            $sshPrivateKey = $_POST['ssh_private_key'];
            $sshPrivateKeyPassword = $_POST['ssh_private_key_password'];


            if ($data['error'] == '') {
                $server = new NetworkServer();

                $server->setName($serverName);
                $server->setIp($serverIp);
                $server->setLoginUser($loginUser);
                $server->setPort($ftpPort);
                $server->setConnectionStatus('Testing');
                $server->setSshKey($sshPrivateKey, $this->getEncryptionService());
                $server->setSshPassword($sshPrivateKeyPassword, $this->getEncryptionService());

                try {
                    $em->persist($server);
                    $em->flush();
                    $data['success'] = "Server Created we will test the connection automatically!";
                } catch (\Exception $e) {
                    $data['error'] = "An unknown error occoured, please Check the provided information";
                    return $this->render('admin/network/create.network.admin.html.twig' , $data);

                }

                $data['success'] = "Admin Created";

            }




        }else{

        }




        // replace this example code with whatever you need
        return $this->render('admin/network/create.network.admin.html.twig' , $data);
    }

    /**
     * @return EncryptionService
     */
    public function getEncryptionService(){
        $encryption_params = $this->container->getParameter('encryption');
        return new EncryptionService($encryption_params);
    }
}
