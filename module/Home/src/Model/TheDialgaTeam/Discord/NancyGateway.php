<?php

namespace Home\Model\TheDialgaTeam\Discord;

use Home\Model\TheDialgaTeam\Discord\Table\DiscordAppTable;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Hydrator\ClassMethods;
use Zend\Json\Json;

/**
 * Class NancyGateway
 * @package Home\Model\TheDialgaTeam\Discord
 */
class NancyGateway
{
    /**
     * Nancy gateway url.
     * @var string
     */
    private $url = 'http://127.0.0.1';

    /**
     * Nancy gateway port.
     * @var int
     */
    private $port = 5000;

    /**
     * NancyGateway constructor.
     * @param null|array $options Nancy gateway options.
     */
    public function __construct($options = null)
    {
        if (isset($options) && is_array($options)) {
            if (isset($options['url']) && !empty($options['url']))
                $this->url = $options['url'];

            if (isset($options['port']) && !empty($options['port']))
                $this->port = $options['port'];
        }
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
        $client = new Client();

        $request = new Request();
        $request->setUri($this->generateAPIUrl($route));
        $request->setMethod(Request::METHOD_GET);

        $response = $client->send($request);
        $json = $response->getBody();

        return Json::decode($json, Json::TYPE_ARRAY);
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