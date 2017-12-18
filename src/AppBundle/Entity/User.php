<?php

namespace AppBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Service\EncryptionService;

/**
 * User
 */
class User implements UserInterface
{


    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $status;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return User
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getRoles()
    {
        if ($this->getAdmin() == 1)
        {
            $roles = array(
                'USER_ROLE' => "ROLE_USER",
                'USER_ROLE_2' => "ROLE_ADMIN",
            );
        } else {
            $roles = array(
                'USER_ROLE' => "ROLE_USER",
            );

        }




        return $roles;

    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @var int
     */
    private $admin;


    /**
     * Set admin
     *
     * @param integer $admin
     *
     * @return User
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * Get admin
     *
     * @return integer
     */
    public function getAdmin()
    {
        return $this->admin;
    }
    /**
     * @var string
     */
    private $apiKey;


    /**
     * Set apiKey
     *
     * @param string $apiKey
     *
     * @return User
     */
    public function setApiKey($apiKey , EncryptionService $encryptionService)
    {
        $return = $encryptionService->encrypt($apiKey);
        $this->apiKey = $return;

        return $this;
    }

    /**
     * Get apiKey
     *
     * @return string
     */
  public function getApiKey(EncryptionService $encryptionService)
{
    $key = $this->apiKey;

    $encKey = $encryptionService->decrypt($key);

    return $encKey;
}
    /**
     * @var integer
     */
    private $tfaStatus;

    /**
     * @var string
     */
    private $tfaSecret;


    /**
     * Set tfaStatus
     *
     * @param integer $tfaStatus
     *
     * @return User
     */
    public function setTfaStatus($tfaStatus)
    {
        $this->tfaStatus = $tfaStatus;

        return $this;
    }

    /**
     * Get tfaStatus
     *
     * @return integer
     */
    public function getTfaStatus()
    {
        return $this->tfaStatus;
    }

    /**
     * Set tfaSecret
     *
     * @param string $tfaSecret
     *
     * @return User
     */
    public function setTfaSecret($tfaSecret)
    {
        $this->tfaSecret = $tfaSecret;

        return $this;
    }

    /**
     * Get tfaSecret
     *
     * @return string
     */
    public function getTfaSecret()
    {

        return $this->tfaSecret;
    }
}
