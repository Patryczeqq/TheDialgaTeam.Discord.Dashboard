<?php

namespace Home\Model\TheDialgaTeam\Discord;

use Home\Model\TheDialgaTeam\Discord\Table\DiscordApp;

/**
 * Class DiscordBot
 * @package Home\Model\TheDialgaTeam\Discord
 */
class DiscordBot
{
    /**
     * @var DiscordApp
     */
    public $discordApp;

    /**
     * DiscordBot Gateway Url.
     * @var string
     */
    private $url = 'http://127.0.0.1';

    /**
     * DiscordBot Gateway Port.
     * @var int
     */
    private $port = 5000;

    /**
     * DiscordBot constructor.
     * @param $options array DiscordBot Gateway Options.
     */
    public function __construct($options = null)
    {
        if (isset($options) && is_array($options)) {
            if (isset($options['url']) && !empty($options['url']))
                $this->url = $options['url'];

            if (isset($options['port']) && !empty($options['port']))
                $this->port = $options['port'];
        }

        $this->discordApp = new DiscordApp($this->generateAPIUrl('discordAppModel'));
    }

    /**
     * Generate DiscordBot API Url.
     * @param $endpoint string
     * @return string DiscordBot API Url.
     */
    private function generateAPIUrl($endpoint)
    {
        return sprintf('%s:%s/%s', $this->url, $this->port, $endpoint);
    }
}