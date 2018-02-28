<?php
/**
 * Created by PhpStorm.
 * User: will
 * Date: 12/12/17
 * Time: 11:35
 */

namespace AppBundle\Service;

use AppBundle\Entity\NetworkServer;
use AppBundle\Entity\ServerTemplate;
use AppBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use ServerBundle\Entity\GameServer;
use Symfony\Component\Config\Definition\Exception\Exception;


class NetworkServerService
{

    /**
     * @var EncryptionService
     */
    private $encryptionService;

    /**
     * @var NetworkServer
     */
    private $server;

    /**
     * @var ObjectManager
     */
    private $em;


    /**
     * Location where the daemon stores its scripts
     *
     * @var string
     */
    protected $scriptLocation = "/usr/local/sp/bin/";


    /**
     * NetworkServerService constructor.
     *
     * @param EncryptionService $encryptionService
     *          A instance of the encryption service
     *
     * @param NetworkServer $server
     *          A server Object
     */
    public function __construct(EncryptionService $encryptionService, NetworkServer $server , ObjectManager $em)
    {
        $this->encryptionService = $encryptionService;
        $this->server = $server;
        $this->em = $em;


    }

    public function connectionTest()
    {
        // Set our variables
        $host = $this->server->getIp();
        $loginUser = $this->server->getLoginUser();
        $sshKey = $this->server->getSshKey($this->encryptionService);
        $sshKeyPassword = $this->server->getSshPassword($this->encryptionService);

        // Load our key file
        $keyFile = new RSA();
        $keyFile->loadKey($sshKey);

        if ($sshKeyPassword !== '') {
            error_log("Setting Password");
            $keyFile->setPassword($sshKeyPassword);
        }

        // Try our connection
        try {
            $connection = new SSH2($host);
            $connectionTest = $connection->login($loginUser, $keyFile);



            return $connectionTest;
        } catch (Exception $e) {
            return false;
        }

    }

    /**
     * Returns a connection if details are valid
     *
     * @return bool|SSH2
     */
    public function masterConnect()
    {
        // Set our variables
        $host = $this->server->getIp();
        $loginUser = $this->server->getLoginUser();
        $sshKey = $this->server->getSshKey($this->encryptionService);
        $sshKeyPassword = $this->server->getSshPassword($this->encryptionService);

        // Load our key file
        $keyFile = new RSA();
        $keyFile->loadKey($sshKey);
        if ($sshKeyPassword !== '') {
            $keyFile->setPassword($sshKeyPassword);
        }

        // Try our connection
        try {
            $connection = new SSH2($host);
            $connection->login($loginUser, $keyFile);

            return $connection;
        } catch (Exception $e) {
            return false;
        }

    }

    /**
     * Connects using a Users credentials to a server.
     *
     * @param $loginName
     * @param $loginPassword
     * @return bool|SSH2
     */
    public function userConnect($loginName, $loginPassword)
    {
        $host = $this->server->getIp();

        // Try our connection
        try {
            $connection = new SSH2($host);
            $connection->login($loginName, $loginPassword);

            return $connection;
        } catch (Exception $e) {
            return false;
        }

    }


    /**
     * Runs a command on the server
     *
     * @param $cmd
     * @return bool
     */
    public function runCMD($cmd)
    {

        $connection = $this->masterConnect();

        $command = $this->scriptLocation . $cmd;
        try{
            $connection->exec($command);
            error_log($command);
            return true;
        }catch (Exception $e)
        {
            return false;
        }


    }

    /**
     * Creates a Game server Template on the remote server
     */
    public function createTemplate($template_id , $steam_name , NetworkServer $networkServer , $callbackUrl)
    {
        $serverId = $networkServer->getId();
        $this->steamInstall($callbackUrl , $steam_name , $template_id , $serverId);


    }

    /**
     * Deletes a Game server Template on the remote server
     */
    public function deleteTemplate(ServerTemplate $template)
    {
        $cmd = "DeleteTemplate -i ". $template->getId();
        $this->runCMD($cmd);

        $this->em->remove($template);
        $this->em->flush();

    }
    /**
     * Deletes a Game server Template on the remote server will NOT update the database.
     */
    public function deleteTemplateNoDb(ServerTemplate $template)
    {
        $cmd = "DeleteTemplate -i ". $template->getId();
        $this->runCMD($cmd);

    }

    function steamInstall($callbackUrl , $steam_name , $tpl_id , $networkServer )
    {
        $cfg_steam_auth = "";

        // TODO: ADD THESE DETAILS TO THE PARAMETERS
        $steam_user = "servers4all";
        $steam_pass = "Servers4all16!";
        

        $cmd  = "SteamCMDInstall -g '$steam_name' -i $tpl_id -l '$steam_user' -p '$steam_pass' -c '$cfg_steam_auth' -u '$callbackUrl' >> /dev/null 2>&1 &";

        $this->runCMD($cmd);
    }

    public function serverCreation(User $user , NetworkServer $networkserver , ServerTemplate $template , $callbackURL , $port)
    {

        $userRemoteName = $user->getServerUser();
        $ip = $networkserver->getIp();


        $templateId = $template->getId();


        $cmd = "CreateServer -u $userRemoteName -i $ip -p $port -x $templateId -c $callbackURL ";

        $this->runCMD($cmd);
    }

    public function restartServer($startCmd , GameServer $gameServer, User $user , ObjectManager $em)
    {
        $serverIp = $gameServer->getIp();
        $serverId = $gameServer->getId();
        $serverPort = $gameServer->getPort();

        $template = $em->getRepository('AppBundle:ServerTemplate')->find($gameServer->getTemplateId());
        $workDir = $template->getSteamName();

        $serverUsername = $user->getUsername();
        $pidFile = $serverUsername."_".$serverId;


        $ssh_cmd = "Restart -u $serverUsername -i $serverIp -p $serverPort -P $pidFile -w $workDir -o '$startCmd'";
        $this->runCMD($ssh_cmd);

        $gameServer->setPid($pidFile);
        $gameServer->setStatus("Starting");
        
        $em->persist($gameServer);
        $em->flush();
    }

    public function stopServer(GameServer $gameServer, User $user , ObjectManager $em)
    {
        $serverIp = $gameServer->getIp();
        $serverPort = $gameServer->getPort();
        $serverLocation = $gameServer->getLocation();
        $serverPid = $gameServer->getPid();
        $serverUsername = $user->getUsername();


        $ssh_cmd = "Stop -u $serverUsername -i $serverIp -p $serverPort  $serverLocation $serverPid ";
        $this->runCMD($ssh_cmd);

        $gameServer->setPid(null);
        $gameServer->setStatus("Offline");

        $em->persist($gameServer);
        $em->flush();
    }

    public function deleteServer(User $user , GameServer $gameServer)
    {
        $serverIp = $gameServer->getIp();
        $serverPort = $gameServer->getPort();
        $serverUser = $user->getServerUser();

        $ssh_cmd  = "DeleteServer -u $serverUser -i $serverIp -p $serverPort";
        $this->runCMD($ssh_cmd);

    }

    public function reinstallServer(GameServer $gameServer , ServerTemplate $template , $callbackUrl , User $user)
    {
        $this->deleteServer($user , $gameServer);
        sleep(5);
        $this->serverCreation($user , $this->server , $template , $callbackUrl , $gameServer->getPort());

        $gameServer->setStatus("Reinstalling");
        $this->em->persist($gameServer);
        $this->em->flush();


    }



}