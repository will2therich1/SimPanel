<?php

namespace ServerBundle\Entity;

/**
 * gameServer
 */
class GameServer
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $templateId;

    /**
     * @var string
     */
    private $serverName;

    /**
     * @var string
     */
    private $gameName;

    /**
     * @var int
     */
    private $ownerId;

    /**
     * @var string
     */
    private $startupCommand;

    /**
     * @var string
     */
    private $updateCommand;

    /**
     * @var string
     */
    private $playerSlots;

    /**
     * @var string
     */
    private $ram;


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
     * Set templateId
     *
     * @param integer $templateId
     *
     * @return GameServer
     */
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;

        return $this;
    }

    /**
     * Get templateId
     *
     * @return int
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * Set serverName
     *
     * @param string $serverName
     *
     * @return GameServer
     */
    public function setServerName($serverName)
    {
        $this->serverName = $serverName;

        return $this;
    }

    /**
     * Get serverName
     *
     * @return string
     */
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * Set gameName
     *
     * @param string $gameName
     *
     * @return GameServer
     */
    public function setGameName($gameName)
    {
        $this->gameName = $gameName;

        return $this;
    }

    /**
     * Get gameName
     *
     * @return string
     */
    public function getGameName()
    {
        return $this->gameName;
    }

    /**
     * Set ownerId
     *
     * @param integer $ownerId
     *
     * @return GameServer
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    /**
     * Get ownerId
     *
     * @return int
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * Set startupCommand
     *
     * @param string $startupCommand
     *
     * @return GameServer
     */
    public function setStartupCommand($startupCommand)
    {
        $this->startupCommand = $startupCommand;

        return $this;
    }

    /**
     * Get startupCommand
     *
     * @return string
     */
    public function getStartupCommand()
    {
        return $this->startupCommand;
    }

    /**
     * Set updateCommand
     *
     * @param string $updateCommand
     *
     * @return GameServer
     */
    public function setUpdateCommand($updateCommand)
    {
        $this->updateCommand = $updateCommand;

        return $this;
    }

    /**
     * Get updateCommand
     *
     * @return string
     */
    public function getUpdateCommand()
    {
        return $this->updateCommand;
    }

    /**
     * Set playerSlots
     *
     * @param string $playerSlots
     *
     * @return GameServer
     */
    public function setPlayerSlots($playerSlots)
    {
        $this->playerSlots = $playerSlots;

        return $this;
    }

    /**
     * Get playerSlots
     *
     * @return string
     */
    public function getPlayerSlots()
    {
        return $this->playerSlots;
    }

    /**
     * Set ram
     *
     * @param string $ram
     *
     * @return GameServer
     */
    public function setRam($ram)
    {
        $this->ram = $ram;

        return $this;
    }

    /**
     * Get ram
     *
     * @return string
     */
    public function getRam()
    {
        return $this->ram;
    }
    /**
     * @var string
     */
    private $queryEngine;


    /**
     * Set queryEngine
     *
     * @param string $queryEngine
     *
     * @return GameServer
     */
    public function setQueryEngine($queryEngine)
    {
        $this->queryEngine = $queryEngine;

        return $this;
    }

    /**
     * Get queryEngine
     *
     * @return string
     */
    public function getQueryEngine()
    {
        return $this->queryEngine;
    }
    /**
     * @var integer
     */
    private $port;


    /**
     * Set port
     *
     * @param integer $port
     *
     * @return GameServer
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port
     *
     * @return integer
     */
    public function getPort()
    {
        return $this->port;
    }
    /**
     * @var string
     */
    private $ip;


    /**
     * Set ip
     *
     * @param string $ip
     *
     * @return GameServer
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     *
     * returns all the server information in one function.
     *
     * @return array
     */
    public function getServerInfomation()
    {
        $returnData = [];

        // Ip Related things
        $returnData['ip'] = $this->getIp();
        $returnData['port'] = $this->getPort();
        $returnData['fullIp'] = $this->getIp() .":". $this->getPort();

        // All other server related things.
        $returnData['id'] = $this->getId();
        $returnData['serverName'] = $this->getServerName();
        $returnData['ownerId'] = $this->getOwnerId();
        $returnData['playerSlots'] = $this->getPlayerSlots();
        $returnData['ram'] = $this->getRam();
        $returnData['startCMD'] = $this->getStartupCommand();
        $returnData['updateCMD'] = $this->getUpdateCommand();
        $returnData['templateId'] = $this->getTemplateId();
        $returnData['queryEngine'] = $this->getQueryEngine();
        $returnData['status'] = $this->getStatus();

        return $returnData;

    }
    /**
     * @var string
     */
    private $status;


    /**
     * Set status
     *
     * @param string $status
     *
     * @return GameServer
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
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $pid;


    /**
     * Set location
     *
     * @param string $location
     *
     * @return GameServer
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
     * Set pid
     *
     * @param string $pid
     *
     * @return GameServer
     */
    public function setPid($pid)
    {
        $this->pid = $pid;

        return $this;
    }

    /**
     * Get pid
     *
     * @return string
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * In the server Startup/Update command:
     *
     * {steam.name} - The steam name of the game being updated eg (340)
     * {server.ip} - The Servers Ip
     * {server.port} - The Servers Port
     *
     * This function will turn these into what they should be
     *
     *
     * @param $startCMD
     * @param $server
     * @param $template
     *
     * @return string
     */
    public function formatStartCMD($startCMD , $server , $template)
    {
        $string_steam_name_replace = str_replace('{steam.name}' , $template->getSteamName() , $startCMD);

        $string_server_ip_replace = str_replace('{server.ip}' , $server->getIp() , $string_steam_name_replace);

        $string_template_port_replace = str_replace('{server.port}' , $server->getPort() , $string_server_ip_replace);

        $string_player_slots_replace = str_replace('{player.slots}' , $server->getPlayerSlots() , $string_template_port_replace);

        // Now we add the extra startup params addable by a user,
        $startupCommand = $string_player_slots_replace . $this->getStartupExtra();

        return $startupCommand;


    }

    /**
     * @var string
     */
    private $startupExtra;


    /**
     * Set startupExtra
     *
     * @param string $startupExtra
     *
     * @return GameServer
     */
    public function setStartupExtra($startupExtra)
    {
        $this->startupExtra = $startupExtra;

        return $this;
    }

    /**
     * Get startupExtra
     *
     * @return string
     */
    public function getStartupExtra()
    {
        return $this->startupExtra;
    }
}
