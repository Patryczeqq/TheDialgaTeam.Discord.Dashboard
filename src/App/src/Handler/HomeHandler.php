<?php

namespace App\Handler;

use App\Constant\Session;
use App\Form\GuildSelectionForm;
use App\Form\Home\BotSelectionForm;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class HomeHandler extends BaseFormHandler
{
    protected function onProcess(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Return vars
        $isLoggedIn = false;
        $guildSelectionForm = null;
        $botSelectionForm = null;
        $selectedBotInstance = null;
        $user = null;
        $error = null;

        if ($this->session->has(Session::DISCORD_OAUTH2_ACCESS_TOKEN)) {
            $isLoggedIn = true;
            $guilds = array();

            try {
                $guilds = $this->getDiscordClient()->user->getCurrentUserGuilds(array());
                $user = $this->getDiscordClient()->user->getCurrentUser(array());
            } catch (\Exception $ex) {
                $this->session->clear();
                $error = $ex->getMessage();
            }

            $guildSelectionForm = new GuildSelectionForm($this->guard, $this->session, $guilds);
        }

        try {
            $discordAppTables = $this->nancyGateway->getDiscordAppTable();
        } catch (\Exception $ex) {
            $discordAppTables = array();
            $this->session->clear();
            $error = $ex->getMessage();
        }

        if ($this->session->has(Session::CLIENT_ID)) {
            foreach ($discordAppTables as $discordAppTable) {
                if ($discordAppTable->getClientId() != $this->session->get(Session::CLIENT_ID))
                    continue;

                $selectedBotInstance = $discordAppTable;
                break;
            }
        }

        $botSelectionForm = new BotSelectionForm($this->guard, $this->session, $discordAppTables);

        if (isset($this->get['error'])) {
            if (is_array($this->get['error']))
                $error = join("\n", $this->get['error']);
            else
                $error = $this->get['error'];
        }

        $this->templateRenderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'isLoggedIn', $isLoggedIn);
        $this->templateRenderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'user', $user);

        return new HtmlResponse($this->templateRenderer->render('app::home', [
            'botSelectionForm' => $botSelectionForm,
            'guildSelectionForm' => $guildSelectionForm,
            'selectedBotInstance' => $selectedBotInstance,
            'error' => $error
        ]));
    }
}