<?php

namespace App\Handler;

use App\Constant\Session;
use App\Form\GuildSelectionForm;
use App\Form\Home\BotSelectionForm;
use App\Form\Home\RefreshGuildsForm;
use App\Handler\BaseForm\BaseFormHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class HomeHandler extends BaseFormHandler
{
    protected function onProcess(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Global Template Vars
        $isLoggedIn = false;
        $user = null;

        $guildSelectionForm = null;
        $botSelectionForm = null;
        $refreshGuildsForm = null;
        $selectedBotInstance = null;

        $error = null;

        if ($this->session->has(Session::DISCORD_OAUTH2_ACCESS_TOKEN)) {
            // User have authenticate discord oauth2 access token.
            $isLoggedIn = true;
            $guilds = array();

            $refreshGuildsForm = new RefreshGuildsForm($this->guard, $this->session);

            try {
                if (isset($this->post['action']) && $this->post['action'] == 'refresh_discord_client_models') {
                    $refreshGuildsForm->setData($this->post);

                    if (!$refreshGuildsForm->isValid())
                        $this->getFormError($refreshGuildsForm);

                    $user = $this->getDiscordClientCurrentUser(false);
                    $guilds = $this->getDiscordClientCurrentUserGuilds(false);
                } else {
                    $user = $this->getDiscordClientCurrentUser();
                    $guilds = $this->getDiscordClientCurrentUserGuilds();
                }
            } catch (\Exception $ex) {
                $this->session->clear();
                $error = $ex->getMessage();
            }

            $guildSelectionForm = new GuildSelectionForm($this->guard, $this->session, $guilds);
        }

        $discordAppTables = array();

        try {
            $discordAppTables = $this->nancyGateway->getDiscordAppTable();
        } catch (\Exception $ex) {
            $this->session->clear();
            $error = $ex->getMessage();
        }

        $botSelectionForm = new BotSelectionForm($this->guard, $this->session, $discordAppTables);

        if ($this->session->has(Session::CLIENT_ID)) {
            foreach ($discordAppTables as $discordAppTable) {
                if ($discordAppTable->getClientId() != $this->session->get(Session::CLIENT_ID))
                    continue;

                $selectedBotInstance = $discordAppTable;
                break;
            }
        }

        if (isset($this->get['error'])) {
            if (is_array($this->get['error']))
                $error = join("\n", $this->get['error']);
            else
                $error = $this->get['error'];
        }

        $this->templateRenderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'isLoggedIn', $isLoggedIn);
        $this->templateRenderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'user', $user);

        return new HtmlResponse($this->templateRenderer->render('app::home', [
            'layout' => "layout::home",
            'botSelectionForm' => $botSelectionForm,
            'guildSelectionForm' => $guildSelectionForm,
            'selectedBotInstance' => $selectedBotInstance,
            'refreshGuildsForm' => $refreshGuildsForm,
            'error' => $error
        ]));
    }
}