<?php

namespace AppBundle\Controller;

use AppBundle\Service\EncryptionService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\NetworkServer;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\NetworkServerService;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
        $data['branding'] = $this->getSiteInformation();
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
            }




        }else{

        }




        // replace this example code with whatever you need
        return $this->render('admin/network/create.network.admin.html.twig' , $data);
    }

    /**
     * @Route("/admin/network/{id}/connectiontest", name="NetworkServerConnectionTest")
     */
    public function networkConnectiontest(Request $request)
    {
        // Get Doctrine
        $em = $this->getDoctrine()->getManager();


        // Create our Data Array
        $data = [];
        $data['currentUser'] = $this->getUser()->getUserInfo();
        $data['branding'] = $this->getSiteInformation();
        $data['active'] = 'Network';
        $data['success'] = '';
        $data['error'] = '';

        $server =$this->getDoctrine()->getManager()->getRepository('AppBundle:NetworkServer')->find($request->attributes->get('id'));
        error_log($server->getId());

        $networkManager = new NetworkServerService($this->getEncryptionService() , $server);
        $connectionStatus = $networkManager->connectionTest();

        error_log(print_r($connectionStatus));
        if ($connectionStatus === true)
        {
            $server->setConnectionStatus("Connected!");
            $em->persist($server);
            $em->flush();
        }else{
            $server->setConnectionStatus("Connection Failed!");
            $em->persist($server);
            $em->flush();
        }



        // replace this example code with whatever you need
        return new RedirectResponse('/admin/network');
    }

    /**
     * @return EncryptionService
     */
    public function getEncryptionService(){
        $encryption_params = $this->container->getParameter('encryption');
        return new EncryptionService($encryption_params);
    }

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
    public function getSetting($settingName)
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
