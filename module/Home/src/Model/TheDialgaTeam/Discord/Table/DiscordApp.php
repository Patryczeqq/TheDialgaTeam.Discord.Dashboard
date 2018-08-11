<?php

namespace Home\Model\TheDialgaTeam\Discord\Table;

use Home\Model\TheDialgaTeam\Discord\Table\Model\DiscordAppModel;
use Zend\Hydrator\ClassMethods;

/**
 * Class DiscordApp
 * @package Home\Model\TheDialgaTeam\Discord\Table
 */
class DiscordApp extends TableGateway
{
    /**
     * @param $clientId
     * @return DiscordAppModel|object
     */
    public function getDiscordAppModel($clientId)
    {
        $jsonObject = $this->getTable(['clientId' => $clientId]);

        return (new ClassMethods())->hydrate($jsonObject, new DiscordAppModel());
    }

    /**
     * @return DiscordAppModel[]
     */
    public function getDiscordAppModels()
    {
        $jsonObject = $this->getTable();
        $result = array();

        foreach ($jsonObject as $key => $value) {
            $result[] = (new ClassMethods())->hydrate($value, new DiscordAppModel());
        }

        return $result;
    }
}