<?php

namespace App\TheDialgaTeam\Discord\Table;

/**
 * Class DiscordAppTable
 * @package App\TheDialgaTeam\Discord\Table
 */
class DiscordAppTable
{
    /**
     * @var int
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
     * @var string
     */
    private $lastUpdateCheck;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
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
     * @return string
     */
    public function getLastUpdateCheck(): string
    {
        return $this->lastUpdateCheck;
    }

    /**
     * @param string $lastUpdateCheck
     */
    public function setLastUpdateCheck(string $lastUpdateCheck): void
    {
        $this->lastUpdateCheck = $lastUpdateCheck;
    }
}