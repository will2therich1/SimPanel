<?php

namespace AppBundle\Entity;

/**
 * ServerTemplate
 */
class ServerTemplate
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $networkId;

    /**
     * @var int
     */
    private $configId;

    /**
     * @var string
     */
    private $steamPercentage;

    /**
     * @var string
     */
    private $dateCreated;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $size;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $location;


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
     * Set networkId
     *
     * @param integer $networkId
     *
     * @return ServerTemplate
     */
    public function setNetworkId($networkId)
    {
        $this->networkId = $networkId;

        return $this;
    }

    /**
     * Get networkId
     *
     * @return int
     */
    public function getNetworkId()
    {
        return $this->networkId;
    }

    /**
     * Set configId
     *
     * @param integer $configId
     *
     * @return ServerTemplate
     */
    public function setConfigId($configId)
    {
        $this->configId = $configId;

        return $this;
    }

    /**
     * Get configId
     *
     * @return int
     */
    public function getConfigId()
    {
        return $this->configId;
    }

    /**
     * Set steamPercentage
     *
     * @param string $steamPercentage
     *
     * @return ServerTemplate
     */
    public function setSteamPercentage($steamPercentage)
    {
        $this->steamPercentage = $steamPercentage;

        return $this;
    }

    /**
     * Get steamPercentage
     *
     * @return string
     */
    public function getSteamPercentage()
    {
        return $this->steamPercentage;
    }

    /**
     * Set dateCreated
     *
     * @param string $dateCreated
     *
     * @return ServerTemplate
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return string
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return ServerTemplate
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

    /**
     * Set size
     *
     * @param string $size
     *
     * @return ServerTemplate
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return ServerTemplate
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set location
     *
     * @param string $location
     *
     * @return ServerTemplate
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }
    /**
     * @var string
     */
    private $steam_name;


    /**
     * Set steamName
     *
     * @param string $steamName
     *
     * @return ServerTemplate
     */
    public function setSteamName($steamName)
    {
        $this->steam_name = $steamName;

        return $this;
    }

    /**
     * Get steamName
     *
     * @return string
     */
    public function getSteamName()
    {
        return $this->steam_name;
    }
    /**
     * @var string
     */
    private $template_name;


    /**
     * Set templateName
     *
     * @param string $templateName
     *
     * @return ServerTemplate
     */
    public function setTemplateName($templateName)
    {
        $this->template_name = $templateName;

        return $this;
    }

    /**
     * Get templateName
     *
     * @return string
     */
    public function getTemplateName()
    {
        return $this->template_name;
    }

    public function getTemplateDetails()
    {
        $data = [];

        $data['id'] = $this->getId();
        $data['name'] = $this->getTemplateName();
        $data['steamGameId'] = $this->getSteamName();
        $data['templateStatus'] = $this->getStatus();
        $data['templateSize'] = $this->getSize();
        $data['description'] = $this->getDescription();
        $data['networkId'] = $this->getNetworkId();
        $data['defaultConfigId'] = $this->getConfigId();

        return $data;
    }

}
