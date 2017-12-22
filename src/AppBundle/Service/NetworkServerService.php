<?php
/**
 * Created by PhpStorm.
 * User: will
 * Date: 12/12/17
 * Time: 11:35
 */

namespace AppBundle\Service;

use AppBundle\Entity\NetworkServer;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
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
    public function __construct(EncryptionService $encryptionService, NetworkServer $server)
    {
        $this->encryptionService = $encryptionService;
        $this->server = $server;


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

}