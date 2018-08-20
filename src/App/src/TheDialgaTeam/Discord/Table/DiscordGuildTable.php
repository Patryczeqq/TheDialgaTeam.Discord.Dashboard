<?php

namespace App\TheDialgaTeam\Discord\Table;

class DiscordGuildTable
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $guildId;

    /**
     * @var string|null
     */
    private $prefix;

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
    public function getGuildId(): ?string
    {
        return $this->guildId;
    }

    /**
     * @param null|string $guildId
     */
    public function setGuildId(?string $guildId): void
    {
        $this->guildId = $guildId;
    }

    /**
     * @return null|string
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * @param null|string $prefix
     */
    public function setPrefix(?string $prefix): void
    {
        $this->prefix = $prefix;
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