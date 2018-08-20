<?php

namespace App\TheDialgaTeam\Discord\Table;

use DateTime;

/**
 * Class DiscordAppTable
 * @package App\TheDialgaTeam\Discord\Table
 */
class DiscordAppTable
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $clientId;

    /**
     * @var string|null
     */
    private $clientSecret;

    /**
     * @var string|null
     */
    private $appName;

    /**
     * @var string|null
     */
    private $appDescription;

    /**
     * @var string|null
     */
    private $botToken;

    /**
     * @var DateTime|null
     */
    private $lastUpdateCheck;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return null|string
     */
    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    /**
     * @param null|string $clientId
     */
    public function setClientId(?string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return null|string
     */
    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    /**
     * @param null|string $clientSecret
     */
    public function setClientSecret(?string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return null|string
     */
    public function getAppName(): ?string
    {
        return $this->appName;
    }

    /**
     * @param null|string $appName
     */
    public function setAppName(?string $appName): void
    {
        $this->appName = $appName;
    }

    /**
     * @return null|string
     */
    public function getAppDescription(): ?string
    {
        return $this->appDescription;
    }

    /**
     * @param null|string $appDescription
     */
    public function setAppDescription(?string $appDescription): void
    {
        $this->appDescription = $appDescription;
    }

    /**
     * @return null|string
     */
    public function getBotToken(): ?string
    {
        return $this->botToken;
    }

    /**
     * @param null|string $botToken
     */
    public function setBotToken(?string $botToken): void
    {
        $this->botToken = $botToken;
    }

    /**
     * @return DateTime|null
     */
    public function getLastUpdateCheck(): ?DateTime
    {
        return $this->lastUpdateCheck;
    }

    /**
     * @param DateTime|null $lastUpdateCheck
     */
    public function setLastUpdateCheck(?DateTime $lastUpdateCheck): void
    {
        $this->lastUpdateCheck = $lastUpdateCheck;
    }
}