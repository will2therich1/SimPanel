<?php
/**
 * SimPanel User Entity
 *
 * @author William Rich
 * @copyright https://servers4all.documize.com/s/Wm5Pm0A1QQABQ1xw/simpanel/d/WnDQ5EA1QQABQ154/simpanel-license
 */
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements AdvancedUserInterface, \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     */
    private $admin;

    /**
     * @ORM\Column(type="integer")
     */
    private $tfaStatus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tfaSecret;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $serverUser;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $serverPassword;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $subUser;

    /**
     * @ORM\Column(type="array", length=255, nullable=true)
     */
    private $subUserPermissions;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $subUserFor;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $whmcsStatus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $whmcsEmail;

    public function getId()
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTfaStatus(): ?int
    {
        return $this->tfaStatus;
    }

    public function setTfaStatus(int $tfaStatus): self
    {
        $this->tfaStatus = $tfaStatus;

        return $this;
    }

    public function getTfaSecret(): ?string
    {
        return $this->tfaSecret;
    }

    public function setTfaSecret(string $tfaSecret): self
    {
        $this->tfaSecret = $tfaSecret;

        return $this;
    }

    public function getServerUser(): ?string
    {
        return $this->serverUser;
    }

    public function setServerUser(?string $serverUser): self
    {
        $this->serverUser = $serverUser;

        return $this;
    }

    public function getServerPassword(): ?string
    {
        return $this->serverPassword;
    }

    public function setServerPassword(?string $serverPassword): self
    {
        $this->serverPassword = $serverPassword;

        return $this;
    }

    public function getSubUser(): ?int
    {
        return $this->subUser;
    }

    public function setSubUser(?int $subUser): self
    {
        $this->subUser = $subUser;

        return $this;
    }

    public function getSubUserPermissions(): ?string
    {
        return $this->subUserPermissions;
    }

    public function setSubUserPermissions(?string $subUserPermissions): self
    {
        $this->subUserPermissions = $subUserPermissions;

        return $this;
    }

    public function getSubUserFor(): ?int
    {
        return $this->subUserFor;
    }

    public function setSubUserFor(?int $subUserFor): self
    {
        $this->subUserFor = $subUserFor;

        return $this;
    }

    public function getWhmcsStatus(): ?int
    {
        return $this->whmcsStatus;
    }

    public function setWhmcsStatus(?int $whmcsStatus): self
    {
        $this->whmcsStatus = $whmcsStatus;

        return $this;
    }

    public function getWhmcsEmail(): ?string
    {
        return $this->whmcsEmail;
    }

    public function setWhmcsEmail(?string $whmcsEmail): self
    {
        $this->whmcsEmail = $whmcsEmail;

        return $this;
    }

    public function getRoles()
    {
        if ($this->getAdmin() == 1) {
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

    public function getAdmin(): ?int
    {
        return $this->admin;
    }

    public function setAdmin(int $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    // Unneeded function!

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
          $this->id,
          $this->username,
          $this->password,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
          $this->id,
          $this->username,
          $this->password,
          ) = unserialize($serialized);
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return true;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
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


}
