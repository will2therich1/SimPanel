<?php

namespace ApiBundle\Entity;

/**
 * ApiKeys
 */
class ApiKeys
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $ownerId;


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
     * Set apiKey
     *
     * @param string $apiKey
     *
     * @return ApiKeys
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get apiKey
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set ownerId
     *
     * @param string $ownerId
     *
     * @return ApiKeys
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    /**
     * Get ownerId
     *
     * @return string
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }
    /**
     * @var string
     */
    private $name;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return ApiKeys
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @var string
     */
    private $lastUsed;


    /**
     * Set lastUsed
     *
     * @param string $lastUsed
     *
     * @return ApiKeys
     */
    public function setLastUsed($lastUsed)
    {
        $this->lastUsed = $lastUsed;

        return $this;
    }

    /**
     * Get lastUsed
     *
     * @return string
     */
    public function getLastUsed()
    {
        return $this->lastUsed;
    }
}
