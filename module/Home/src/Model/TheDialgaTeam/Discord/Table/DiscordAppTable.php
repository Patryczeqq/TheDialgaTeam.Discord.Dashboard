<?php

namespace Home\Model\TheDialgaTeam\Discord\Table;

class DiscordAppTable
{
    /**
     * @var long
     */
    private $id;

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
    private $appName;

    /**
     * @var string
     */
    private $appDescription;

    /**
     * @var string
     */
    private $botToken;

    /**
     * @var long
     */
    private $lastUpdateCheck;

    /**
     * @return long
     */
    public function getId(): long
    {
        return $this->id;
    }

    /**
     * @param long $id
     */
    public function setId(long $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function getAppName(): string
    {
        return $this->appName;
    }

    /**
     * @param string $appName
     */
    public function setAppName(string $appName): void
    {
        $this->appName = $appName;
    }

    /**
     * @return string
     */
    public function getAppDescription(): string
    {
        return $this->appDescription;
    }

    /**
     * @param string $appDescription
     */
    public function setAppDescription(string $appDescription): void
    {
        $this->appDescription = $appDescription;
    }

    /**
     * @return string
     */
    public function getBotToken(): string
    {
        return $this->botToken;
    }

    /**
     * @param string $botToken
     */
    public function setBotToken(string $botToken): void
    {
        $this->botToken = $botToken;
    }

    /**
     * @return long
     */
    public function getLastUpdateCheck(): long
    {
        return $this->lastUpdateCheck;
    }

    /**
     * @param long $lastUpdateCheck
     */
    public function setLastUpdateCheck(long $lastUpdateCheck): void
    {
        $this->lastUpdateCheck = $lastUpdateCheck;
    }
}