<?php

namespace App\TheDialgaTeam\Discord\Module;

use App\TheDialgaTeam\Discord\NancyGateway;
use App\TheDialgaTeam\Discord\Table\DiscordAppOwnerTable;
use App\TheDialgaTeam\Discord\Table\DiscordAppTable;
use App\TheDialgaTeam\Discord\Table\DiscordGuildTable;
use Zend\Http\Request;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\Strategy\DateTimeFormatterStrategy;

class BaseModule
{
    private $nancyGateway;

    /**
     * BaseModule constructor.
     * @param NancyGateway $nancyGateway
     */
    public function __construct(NancyGateway $nancyGateway)
    {
        $this->nancyGateway = $nancyGateway;
    }

    /**
     * @param string $clientId
     * @return array|DiscordAppTable[]
     * @throws \Exception
     */
    public function getDiscordAppTable($clientId = null)
    {
        $route = isset($clientId) ? "getDiscordAppTable/clientId/$clientId" : 'getDiscordAppTable';
        $jsonArray = $this->nancyGateway->getResponseFromServer($route, Request::METHOD_GET);
        $discordAppTables = array();

        $hydrator = new ClassMethods();
        $hydrator->addStrategy("lastUpdateCheck", new DateTimeFormatterStrategy("Y-m-d\TH:i:s.u?P"));

        foreach ($jsonArray as $discordAppTable) {
            $discordAppTables[] = $hydrator->hydrate($discordAppTable, new DiscordAppTable());
        }

        return $discordAppTables;
    }

    /**
     * @param string $clientId
     * @return array|DiscordAppOwnerTable[]
     * @throws \Exception
     */
    public function getDiscordAppOwnerTable($clientId = null)
    {
        $route = isset($clientId) ? "getDiscordAppOwnerTable/clientId/$clientId" : 'getDiscordAppOwnerTable';
        $jsonArray = $this->nancyGateway->getResponseFromServer($route, Request::METHOD_GET);
        $discordAppOwnerTables = array();

        $hydrator = new ClassMethods();

        foreach ($jsonArray as $discordAppOwnerTable) {
            $discordAppOwnerTables[] = $hydrator->hydrate($discordAppOwnerTable, new DiscordAppOwnerTable());
        }

        return $discordAppOwnerTables;
    }

    /**
     * @param string $clientId
     * @param string $guildId
     * @return object|DiscordGuildTable
     * @throws \Exception
     */
    public function getDiscordGuildTable($clientId, $guildId)
    {
        $jsonArray = $this->nancyGateway->getResponseFromServer("getDiscordGuildTable/clientId/$clientId/guildId/$guildId", Request::METHOD_GET);
        $hydrator = new ClassMethods();

        return $hydrator->hydrate($jsonArray, new DiscordGuildTable());
    }
}