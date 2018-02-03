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

    /**
     * Gets the user info and returns this in an array
     *
     * @return array
     *          Array of user information
     */
    public function getUserInfo(){

        // Generate the user information
        $firstName = $this->getFirstName();
        $lastName = $this->getLastName();
        $fullName = $firstName . " " . $lastName;

        $id = $this->getId();
        $username = $this->getUsername();
        $email = $this->getEmail();

        // Prepare data
        $data = [];
        $data['id'] = $id;
        $data['name'] = $fullName;
        $data['username'] = $username;
        $data['email'] = $email;
        $data['firstName'] = $firstName;
        $data['lastName'] = $lastName;
        $data['subUser'] = $this->getSubUser();

        return $data;
    }

    /**
     * @var string
     */
    private $serverUser;

    /**
     * @var string
     */
    private $serverPassword;


    /**
     * Set serverUser
     *
     * @param string $serverUser
     *
     * @return User
     */
    public function setServerUser($serverUser)
    {
        $this->serverUser = $serverUser;

        return $this;
    }

    /**
     * Get serverUser
     *
     * @return string
     */
    public function getServerUser()
    {
        return $this->serverUser;
    }

    /**
     * Set serverPassword
     *
     * @param string $serverPassword
     *
     * @return User
     */
    public function setServerPassword($serverPassword , EncryptionService $encryptionService)
    {
        $this->serverPassword = $encryptionService->encrypt($serverPassword);

        return $this;
    }

    /**
     * Get serverPassword
     *
     * @return string
     */
    public function getServerPassword(EncryptionService $encryptionService)
    {
        return $encryptionService->decrypt($this->serverPassword);
    }


    /**
     * Generates a random password
     *
     * @param int $length Length of the password
     * @param bool $add_dashes Add dashes to the password
     * @param string $available_sets Rules to use
     *
     * @return bool|string
     */
    public function generatePassword($length = 9, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        if (strpos($available_sets, 'l') !== false) {
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        }
        if (strpos($available_sets, 'u') !== false) {
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }
        if (strpos($available_sets, 'd') !== false) {
            $sets[] = '23456789';
        }
        if (strpos($available_sets, 's') !== false) {
            $sets[] = '!@#$%&*?';
        }
        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++) {
            $password .= $all[array_rand($all)];
        }
        $password = str_shuffle($password);
        if (!$add_dashes) {
            return $password;
        }
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while (strlen($password) > $dash_len) {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }
    /**
     * @var integer
     */
    private $subUser;

    /**
     * @var string
     */
    private $subUserPermissions;


    /**
     * Set subUser
     *
     * @param integer $subUser
     *
     * @return User
     */
    public function setSubUser($subUser)
    {
        $this->subUser = $subUser;

        return $this;
    }

    /**
     * Get subUser
     *
     * @return integer
     */
    public function getSubUser()
    {
        return $this->subUser;
    }

    /**
     * Set subUserPermissions
     *
     * @param string $subUserPermissions
     *
     * @return User
     */
    public function setSubUserPermissions($subUserPermissions)
    {
        $this->subUserPermissions = $subUserPermissions;

        return $this;
    }

    /**
     * Get subUserPermissions
     *
     * @return string
     */
    public function getSubUserPermissions()
    {
        return $this->subUserPermissions;
    }
    /**
     * @var integer
     */
    private $subUserFor;


    /**
     * Set subUserFor
     *
     * @param integer $subUserFor
     *
     * @return User
     */
    public function setSubUserFor($subUserFor)
    {
        $this->subUserFor = $subUserFor;

        return $this;
    }

    /**
     * Get subUserFor
     *
     * @return integer
     */
    public function getSubUserFor()
    {
        return $this->subUserFor;
    }
}
