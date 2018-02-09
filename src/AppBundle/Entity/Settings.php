<?php

namespace AppBundle\Entity;

/**
 * Settings
 */
class Settings
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $settingName;

    /**
     * @var string
     */
    private $settingValue;

    /**
     * @var string
     */
    private $settingUpdatedTime;


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
     * Set settingName
     *
     * @param string $settingName
     *
     * @return Settings
     */
    public function setSettingName($settingName)
    {
        $this->settingName = $settingName;

        return $this;
    }

    /**
     * Get settingName
     *
     * @return string
     */
    public function getSettingName()
    {
        return $this->settingName;
    }

    /**
     * Set settingValue
     *
     * @param string $settingValue
     *
     * @return Settings
     */
    public function setSettingValue($settingValue)
    {
        $this->settingValue = $settingValue;

        return $this;
    }

    /**
     * Get settingValue
     *
     * @return string
     */
    public function getSettingValue()
    {
        return $this->settingValue;
    }

    /**
     * Set settingUpdatedTime
     *
     * @param string $settingUpdatedTime
     *
     * @return Settings
     */
    public function setSettingUpdatedTime($settingUpdatedTime)
    {
        $this->settingUpdatedTime = $settingUpdatedTime;

        return $this;
    }

    /**
     * Get settingUpdatedTime
     *
     * @return string
     */
    public function getSettingUpdatedTime()
    {
        return $this->settingUpdatedTime;
    }
}
