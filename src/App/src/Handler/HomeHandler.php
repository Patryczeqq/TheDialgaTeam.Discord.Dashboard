<?php

namespace App\Handler;

use App\Error\Error;
use App\Form\BotSelectionForm;
use App\Form\GuildSelectionForm;
use App\TheDialgaTeam\Discord\NancyGateway;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class HomeHandler extends BaseFormHandler
{
    public function __construct(TemplateRendererInterface $templateRenderer, NancyGateway $nancyGateway)
    {
        parent::__construct($templateRenderer, $nancyGateway);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->preProcess($request);

        // Return vars
        $isLoggedIn = false;
        $guildSelectionForm = null;
        $botSelectionForm = null;
        $selectedBotInstance = null;
        $user = null;
        $error = null;

        if ($this->session->has('discord_oauth2')) {
            $isLoggedIn = true;
            $guilds = array();

            try {
                $guilds = $this->discordClient->user->getCurrentUserGuilds([]);
            } catch (\Exception $ex) {
                $this->session->clear();
                $error = $ex->getMessage();
            }

            $guildSelectionForm = new GuildSelectionForm($this->guard, $this->session, $guilds);
            $user = $this->discordClient->user->getCurrentUser([]);
        }

        if ($this->session->has('clientId')) {
            $discordAppTables = $this->nancyGateway->getDiscordAppTable($this->session->get('clientId'));

            if (count($discordAppTables) == 0) {
                $this->session->clear();
                $error = Error::ERROR_NANCY_GATEWAY;
            }

            $selectedBotInstance = $discordAppTables[0];
        }

        $botSelectionForm = new BotSelectionForm($this->guard, $this->session, $this->nancyGateway->getDiscordAppTable());

        if (isset($this->get['error'])) {
            if (is_array($this->get['error'])) {
                $error = join('\n', $this->get['error']);
            } else {
                $error = $this->get['error'];
            }
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