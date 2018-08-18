<?php

namespace App\Handler;

use App\Constant\Error;
use App\Constant\Session;
use App\Form\CsrfGuardedForm;
use App\Form\GuildSelectionForm;
use App\Form\Home\BotSelectionForm;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;

class DiscordAppAuthenticationHandler extends BaseFormHandler
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function onProcess(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (isset($this->post['action']) && $this->post['action'] == 'discordAppAuthentication') {
            $discordAppTables = $this->nancyGateway->getDiscordAppTable();

            $botSelectionForm = new BotSelectionForm($this->guard, $this->session, $discordAppTables);
            $botSelectionForm->setData($this->post);

            if (!$botSelectionForm->isValid())
                $this->getFormError($botSelectionForm);

            $botSelectionFormData = $botSelectionForm->getData();
            $clientId = $botSelectionFormData['clientId'];

            $this->session->clear();
            $this->session->set(Session::CLIENT_ID, $clientId);

            $discordOAuth2 = $this->getDiscordOAuth2($clientId);

            return new RedirectResponse($discordOAuth2->getAuthorizationUrl([
                'state' => $this->getCsrfToken(),
                'scope' => ['identify', 'guilds', 'guilds.join']
            ]));
        }

        if (isset($this->post['action']) && $this->post['action'] == 'discordGuildAuthentication') {
            $guilds = $this->getDiscordClient()->user->getCurrentUserGuilds(array());

            $guildSelectionForm = new GuildSelectionForm($this->guard, $this->session, $guilds);
            $guildSelectionForm->setData($this->post);

            if (!$guildSelectionForm->isValid())
                $this->getFormError($guildSelectionForm);

            $guildSelectionFormData = $guildSelectionForm->getData();
            $guildId = $guildSelectionFormData['guildId'];
            $clientId = $this->session->get(Session::CLIENT_ID);

            if (!$this->nancyGateway->checkBotExist($clientId, $guildId)) {
                $discordOAuth2 = $this->getDiscordOAuth2($clientId);

                return new RedirectResponse($discordOAuth2->getAuthorizationUrl([
                    'state' => $this->getCsrfToken(),
                    'scope' => ['identify', 'guilds', 'guilds.join', 'bot'],
                    'guild_id' => $guildId,
                    'permissions' => 0x8
                ]));
            } else {
                return new RedirectResponse($this->urlHelper->generate('dashboard', ['guildId' => $guildId]));
            }
        }

        if (isset($this->get['code']) && isset($this->get['state'])) {
            // If this request is from Discord OAuth2.
            $this->session->set('state', $this->get['state']);
            $csrfGuardedForm = new CsrfGuardedForm($this->guard, $this->session, 'state');
            $csrfGuardedForm->setData($this->get);

            if (!$csrfGuardedForm->isValid())
                $this->getFormError($csrfGuardedForm);

            if (!$this->session->has(Session::CLIENT_ID))
                throw new \Exception(Error::ERROR_INVALID_SESSION);

            $clientId = $this->session->get(Session::CLIENT_ID);

            $discordOAuth2 = $this->getDiscordOAuth2($clientId);

            try {
                $accessToken = $discordOAuth2->getAccessToken('authorization_code', [
                    'code' => $this->get['code']
                ]);

                $this->session->set(Session::DISCORD_OAUTH2_ACCESS_TOKEN, $accessToken->jsonSerialize());

                if (isset($this->get['guild_id']))
                    return new RedirectResponse($this->urlHelper->generate('dashboard', ['guildId' => $this->get['guild_id']]));
                else
                    return new RedirectResponse($this->urlHelper->generate('home'));
            } catch (\Exception $ex) {
                throw new \Exception(Error::ERROR_DISCORD_GATEWAY);
            }
        }

        if (isset($this->get['error']))
            return new RedirectResponse($this->urlHelper->generate('home', [], ['error' => $this->get['error']]));
        else
            throw new \Exception(Error::ERROR_INVALID_REQUEST);
    }
}