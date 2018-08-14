<?php

namespace App\TheDialgaTeam\Discord;

use App\TheDialgaTeam\Discord\Table\DiscordAppTable;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Hydrator\ClassMethods;
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
     * Nancy gateway error handling.
     * @var bool
     */
    private $throwExceptionOnError;

    /**
     * NancyGateway constructor.
     * @param array $config Nancy gateway options.
     */
    public function __construct($config)
    {
        $this->url = $config['url'];
        $this->port = $config['port'];
        $this->throwExceptionOnError = $config['throwExceptionOnError'];
    }

    /**
     * @param null|string $clientId
     * @return array|DiscordAppTable[]
     */
    public function getDiscordAppTable($clientId = null)
    {
        $route = isset($clientId) ? "getDiscordAppTable/clientId/$clientId" : 'getDiscordAppTable';
        $jsonArray = $this->getResponseFromServer($route);
        $discordAppTables = array();

        foreach ($jsonArray as $discordAppTable) {
            $discordAppTables[] = (new ClassMethods())->hydrate($discordAppTable, new DiscordAppTable());
        }

        return $discordAppTables;
    }

    /**
     * @param string $route
     * @return array
     */
    private function getResponseFromServer($route)
    {
        if ($this->throwExceptionOnError) {
            $client = new Client();

            $request = new Request();
            $request->setUri($this->generateAPIUrl($route));
            $request->setMethod(Request::METHOD_GET);

            $response = $client->send($request);
            $json = $response->getBody();

            return Json::decode($json, Json::TYPE_ARRAY);
        } else {
            try {
                $client = new Client();

                $request = new Request();
                $request->setUri($this->generateAPIUrl($route));
                $request->setMethod(Request::METHOD_GET);

                $response = $client->send($request);
                $json = $response->getBody();

                return Json::decode($json, Json::TYPE_ARRAY);
            } catch (\Exception $ex) {
                return array();
            }
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