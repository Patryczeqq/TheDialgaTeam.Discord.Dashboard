<?php

namespace App\Constant;

class Error
{
    public const ERROR_INVALID_SESSION = 'Invalid session. Please try again.';

    public const ERROR_NANCY_GATEWAY = 'Nancy Gateway is currently unavailable. Please try again later.';

    public const ERROR_DISCORD_GATEWAY = 'Discord OAuth2 API is currently unavailable. Please try again later.';

    public const ERROR_INVALID_REQUEST = 'Malformed or Invalid request have been made.';
}