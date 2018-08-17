<?php

namespace App\TheDialgaTeam\Discord;

use App\Constant\Error;
use App\TheDialgaTeam\Discord\Table\DiscordAppTable;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\Strategy\DateTimeFormatterStrategy;
use Zend\Json\Json;

/**
 * Class NancyGateway
 * @package App\TheDialgaTeam\Discord
 */
class NancyGateway
{
    /**
     * Nancy gateway url.
     * @var string
     */
    private $url;

    /**
     * Nancy gateway port.
     * @var int
     */
    private $port;

    /**
     * NancyGateway constructor.
     * @param array $config Nancy gateway options.
     */
    public function __construct($config)
    {
        $this->url = $config['url'];
        $this->port = $config['port'];
    }

    /**
     * @param null|string $clientId
     * @return array|DiscordAppTable[]
     * @throws \Exception
     */
    public function getDiscordAppTable($clientId = null)
    {
        $route = isset($clientId) ? "getDiscordAppTable/clientId/$clientId" : 'getDiscordAppTable';
        $jsonArray = $this->getResponseFromServer($route);
        $discordAppTables = array();

        $hydrator = new ClassMethods();
        $hydrator->addStrategy("lastUpdateCheck", new DateTimeFormatterStrategy("Y-m-d\TH:i:s.u?P"));

        foreach ($jsonArray as $discordAppTable) {
            $discordAppTables[] = $hydrator->hydrate($discordAppTable, new DiscordAppTable());
        }

        return $discordAppTables;
    }

    /**
     * @param string $route
     * @return array
     * @throws \Exception
     */
    private function getResponseFromServer($route)
    {
        try {
            $client = new Client();

            $request = new Request();
            $request->setUri($this->generateAPIUrl($route));
            $request->setMethod(Request::METHOD_GET);

            $response = $client->send($request);
            $json = $response->getBody();

            return Json::decode($json, Json::TYPE_ARRAY);
        } catch (\Exception $ex) {
            throw new \Exception(Error::ERROR_NANCY_GATEWAY);
        }
    }

    /**
     * @param string $endpoint Nancy gateway route.
     * @return string
     */
    private function generateAPIUrl($endpoint)
    {
        return sprintf('%s:%s/%s', $this->url, $this->port, $endpoint);
    }
}