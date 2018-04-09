<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 07/04/2018
 * Time: 21:20
 */

namespace App\Service\Network;


use App\Service\Security\EncryptionService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\NetworkServer;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;

class NetworkServerService
{
    /**
     * @var EntityManagerInterface $em;
     */
    private $em;

    /**
     * @var EncryptionService $encService
     */
    private $encService;

    /**
     * @var NetworkServer
     */
    private $server;

    /**
     * Location where the daemon stores its scripts
     *
     * @var string
     */
    protected $scriptLocation = "/usr/local/sp/bin/";


    public function __construct(EntityManagerInterface $em , EncryptionService $encryptionService)
    {
        $this->em = $em;
        $this->encService = $encryptionService;
    }

    /**
     *
     * Creates us a new network Server
     *
     * @param array $serverData - The server data array as defined below
     *
     * array['server name'] - the servers name
     * array['server ip'] - the users last name
     * array['server ftp port'] - the servers ftp port
     * array['server login user'] - the user needed to log into the server
     * array['server ssh key'] - the servers ssh key
     * array['server ssh key password'] - the password for servers ssh key - NOT NEEDED IF NO PASSWORD
     *
     * @throws \Exception - If anything goes wrong!
     * @return NetworkServer - The new server object
     */
    public function createNewNetworkServer($serverData)
    {
        $newServer = new NetworkServer();


        $newServer->setServerName($serverData['server name']);
        $newServer->setServerIp($serverData['server ip']);
        $newServer->setServerPort($serverData['server ftp port']);
        $newServer->setLoginUser($serverData['server login user']);

        $newServer->setSshKey($this->encService->encrypt($serverData['server ssh key']));
        $newServer->setSshKeyPassword($this->encService->encrypt($serverData['server ssh key password']));
        $newServer->setConnectionStatus('New');

        return $newServer;
    }

    /**
     * Gets the network server via its id.
     *
     * @param $id - id of the server
     * @return NetworkServer|null|object - Server object
     */
    public function getServerById($id)
    {
        return $this->em->getRepository('App:NetworkServer')->find($id);
    }

    public function connectionTest($id)
    {
        // Set our variables
        $server = $this->getServerById($id);

        $host = $server->getServerIp();
        $loginUser = $server->getLoginUser();
        $sshKey = $server->getSshKey($this->encryptionService);
        $sshKeyPassword = $server->getSshKeyPassword($this->encryptionService);
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

}