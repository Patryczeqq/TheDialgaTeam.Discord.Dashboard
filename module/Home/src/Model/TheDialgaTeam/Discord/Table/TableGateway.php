<?php

namespace Home\Model\TheDialgaTeam\Discord\Table;

use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Json\Json;

/**
 * Class TableGateway
 * @package Home\Model\TheDialgaTeam\Discord\Table
 */
abstract class TableGateway
{
    /**
     * DiscordBot Table Gateway Endpoint.
     * @var string
     */
    private $endpoint;

    /**
     * TableGateway constructor.
     * @param $endpoint string
     */
    public function __construct($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * Get table data.
     * @param $searchParams array Specific search parameter.
     * @return array Table in array form.
     */
    protected function getTable($searchParams = null)
    {
        $client = new Client();

        $request = new Request();
        $request->setUri($this->endpoint);
        $request->setMethod(Request::METHOD_GET);

        if (isset($searchParams) && is_array($searchParams)) {
            foreach ($searchParams as $key => $value) {
                $request->getQuery($key, $value);
            }
        }

        $response = $client->send($request);
        $json = $response->getBody();

        return Json::decode($json, Json::TYPE_ARRAY);
    }
}