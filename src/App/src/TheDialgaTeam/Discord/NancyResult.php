<?php

namespace App\TheDialgaTeam\Discord;

class NancyResult
{
    /**
     * @var bool
     */
    private $isSuccess;

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * @param bool $isSuccess
     */
    public function setIsSuccess(bool $isSuccess): void
    {
        $this->isSuccess = $isSuccess;
    }
}