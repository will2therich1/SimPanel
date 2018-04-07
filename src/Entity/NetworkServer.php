<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NetworkServerRepository")
 */
class NetworkServer
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
    private $serverName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $serverIp;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $serverPort;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $loginUser;

    /**
     * @ORM\Column(type="text")
     */
    private $sshKey;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $connectionStatus;

    /**
     * @ORM\Column(type="string", length=255 , nullable=true)
     */
    private $sshKeyPassword;

    public function getId()
    {
        return $this->id;
    }

    public function getServerName(): ?string
    {
        return $this->serverName;
    }

    public function setServerName(string $serverName): self
    {
        $this->serverName = $serverName;

        return $this;
    }

    public function getServerIp(): ?string
    {
        return $this->serverIp;
    }

    public function setServerIp(string $serverIp): self
    {
        $this->serverIp = $serverIp;

        return $this;
    }

    public function getServerPort(): ?string
    {
        return $this->serverPort;
    }

    public function setServerPort(string $serverPort): self
    {
        $this->serverPort = $serverPort;

        return $this;
    }

    public function getLoginUser(): ?string
    {
        return $this->loginUser;
    }

    public function setLoginUser(string $loginUser): self
    {
        $this->loginUser = $loginUser;

        return $this;
    }

    public function getSshKey(): ?string
    {
        return $this->sshKey;
    }

    public function setSshKey(string $sshKey): self
    {
        $this->sshKey = $sshKey;

        return $this;
    }

    public function getConnectionStatus(): ?string
    {
        return $this->connectionStatus;
    }

    public function setConnectionStatus(string $connectionStatus): self
    {
        $this->connectionStatus = $connectionStatus;

        return $this;
    }

    public function getSshKeyPassword(): ?string
    {
        return $this->sshKeyPassword;
    }

    public function setSshKeyPassword(string $sshKeyPassword): self
    {
        $this->sshKeyPassword = $sshKeyPassword;

        return $this;
    }
}
