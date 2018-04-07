<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 07/04/2018
 * Time: 21:20
 */

namespace App\Service\Network;


use App\Service\Core\SettingService;
use App\Service\Security\EncryptionService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\NetworkServer;

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

}