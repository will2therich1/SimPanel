<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SettingRepository")
 */
class Setting
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
    private $settingName;

    /**
     * @ORM\Column(type="text")
     */
    private $settingValue;

    /**
     * @ORM\Column(type="datetime")
     */
    private $settingUpdatedTime;

    public function getId()
    {
        return $this->id;
    }

    public function getSettingName(): ?string
    {
        return $this->settingName;
    }

    public function setSettingName(string $settingName): self
    {
        $this->settingName = $settingName;

        return $this;
    }

    public function getSettingValue(): ?string
    {
        return $this->settingValue;
    }

    public function setSettingValue(string $settingValue): self
    {
        $this->settingValue = $settingValue;

        return $this;
    }

    public function getSettingUpdatedTime()
    {
        return $this->settingUpdatedTime;
    }

    public function setSettingUpdatedTime(\DateTime $settingUpdatedTime): self
    {
        $this->settingUpdatedTime = $settingUpdatedTime;

        return $this;
    }
}
