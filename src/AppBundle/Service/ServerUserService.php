<?php
/**
 * Created by PhpStorm.
 * User: will
 * Date: 12/12/17
 * Time: 11:35
 */

namespace AppBundle\Service;

use AppBundle\Entity\NetworkServer;
use AppBundle\Entity\User;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\Common\Persistence\ObjectManager;

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

    /**
     * @var NetworkServerService
     */
    private $networkService;

    /**
     * Location where the daemon stores its scripts
     *
     * @var string
     */
    protected $scriptLocation = "/usr/local/sp/bin/";

    /**
     * @var User
     */
    private $user;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * ServerUserService constructor.
     *
     * @param EncryptionService $encryptionService
     * @param NetworkServer $server
     * @param \AppBundle\Service\User $user
     * @param ObjectManager $entityManager
     */
    public function __construct(
        EncryptionService $encryptionService,
        NetworkServer $server,
        User $user,
        ObjectManager $entityManager
    ) {
        $this->encryptionService = $encryptionService;
        $this->server = $server;
        $this->user = $user;
        $this->em = $entityManager;

        $this->networkService = new NetworkServerService($this->encryptionService, $this->server , $entityManager);

    }


    public function connect()
    {
        $username = $this->user->getServerUser();
        $password = $this->user->getServerPassword($this->encryptionService);

        if ($username == '') {
            error_log("Creating User");
            $this->createUser($this->user->getUsername());
            $username = $this->user->getServerUser();
            $password = $this->user->getServerPassword($this->encryptionService);
        }

        $connection = $this->networkService->userConnect($username , $password);
        dump($connection);
        return $connection;


    }

    /**
     * Creates a user on the server
     *
     * @param $username
     * @return bool
     */
    public function createUser($username)
    {

        $password = $this->user->generatePassword();
        $cmd = "CreateUser -u '$username' -p '$password'";

        $this->user->setServerUser($username);
        $this->user->setServerPassword($password, $this->encryptionService);
        $this->em->persist($this->user);
        $this->em->flush();

        $this->networkService->runCMD($cmd);

        return true;


    }





}