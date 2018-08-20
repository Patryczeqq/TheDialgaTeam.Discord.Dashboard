<?php

namespace App\TheDialgaTeam\Discord\Table;

/**
 * Class DiscordAppOwnerTable
 * @package App\TheDialgaTeam\Discord\Table
 */
class DiscordAppOwnerTable
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $userId;

    /**
     * @var int|null
     */
    private $discordAppId;

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
    public function getUserId(): ?string
    {
        return $this->userId;
    }

    /**
     * @param null|string $userId
     */
    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return int|null
     */
    public function getDiscordAppId(): ?int
    {
        return $this->discordAppId;
    }

    /**
     * @param int|null $discordAppId
     */
    public function setDiscordAppId(?int $discordAppId): void
    {
        $this->discordAppId = $discordAppId;
    }
}