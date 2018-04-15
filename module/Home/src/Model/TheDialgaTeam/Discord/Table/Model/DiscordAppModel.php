<?php

namespace Home\Model\TheDialgaTeam\Discord\Table\Model;

class DiscordAppModel extends BaseTable
{
    /**
     * @var string
     */
    private $appName;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $botToken;

    /**
     * @var bool
     */
    private $verified;

    /**
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @return string
     */
    public function getBotToken()
    {
        return $this->botToken;
    }

    /**
     * @return bool
     */
    public function getVerified()
    {
        return $this->verified;
    }

    /**
     * @param string $appName
     */
    public function setAppName($appName)
    {
        $this->appName = $appName ?? '';
    }

    /**
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId ?? '';
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret ?? '';
    }

    /**
     * @param string $botToken
     */
    public function setBotToken($botToken)
    {
        $this->botToken = $botToken ?? '';
    }

    /**
     * @param bool $verified
     */
    public function setVerified($verified)
    {
        $this->verified = $verified ?? false;
    }
}