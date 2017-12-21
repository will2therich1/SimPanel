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


class ServerUserService
{

    /**
     * @var EncryptionService
     */
    private $encryptionService;

    /**
     * @var NetworkServer
     */
    private $server;

    private $networkService;


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
        $this->networkService = new NetworkServerService($this->encryptionService, $this->server);

    }


    public function connect()
    {

    }


}