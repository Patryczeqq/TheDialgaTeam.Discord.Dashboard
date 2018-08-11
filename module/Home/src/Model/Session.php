<?php

namespace Home\Model;

/**
 * @property string TheDialgaTeam_Discord_Bot_csrf
 * @property string discordAppModelsJson
 * @property string clientId
 * @property string clientSecret
 * @property string access_token
 * @property string token_type
 * @property string expires_in
 * @property string refresh_token
 * @property string scope
 * @property string session_start_time
 */
class Session
{
    /**
     * Start or resume a session.
     * @return bool
     */
    public function startOrResumeSession()
    {
        return session_start();
    }

    /**
     * Generate a new csrf token.
     * @return string
     * @throws \Exception
     */
    public function generateNewCsrfToken()
    {
        $csrf = md5(random_bytes(16));
        $this->TheDialgaTeam_Discord_Bot_csrf = $csrf;

        return $csrf;
    }

    /**
     * Validate if csrf token is match.
     * @param $token string Csrf token to check.
     * @return bool true if csrf token is match, else false.
     */
    public function validateCsrfToken($token)
    {
        return $token == $this->TheDialgaTeam_Discord_Bot_csrf;
    }

    /**
     * @param $name string
     * @return mixed
     */
    public function __get($name)
    {
        return $_SESSION[$name];
    }

    /**
     * @param $name string
     * @param $value mixed
     */
    public function __set($name, $value)
    {
        $_SESSION[$name] = $value;
    }
}