<?php
/**
 * Created by PhpStorm.
 * User: will
 * Date: 16/02/18
 * Time: 14:56
 */

namespace ServerBundle\Service;

use GameQ\GameQ;

class GameServerService
{

    /**
     * GameServerService constructor.
     */
    public function __construct()
    {

    }

    /**
     *
     * Queries a game server for its status
     *
     * @param $serverIp
     * @param $serverPort
     * @param $queryEngine
     * @return array
     * @throws \Exception
     */
    public function queryServer($serverIp , $serverPort , $queryEngine)
    {
        $gameQ = new GameQ();

        $gameQ->addServer([
            'type' => $queryEngine,
            'host' => $serverIp . ":" . $serverPort,
        ]);

        $query = $gameQ->process();

        return $query;
    }


}