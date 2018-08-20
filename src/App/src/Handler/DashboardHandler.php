<?php

namespace App\Handler;

use App\Constant\Session;
use App\Form\Dashboard\CommandPrefixForm;
use App\Form\Dashboard\NicknameForm;
use App\Form\GuildSelectionForm;
use App\Handler\BaseForm\BaseFormHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class DashboardHandler extends BaseFormHandler
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function onProcess(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Global Template Vars
        $isLoggedIn = false;
        $user = null;
        $guildSelectionForm = null;
        $selectedGuild = null;

        $nicknameForm = new NicknameForm($this->guard, $this->session);
        $commandPrefixForm = new CommandPrefixForm($this->guard, $this->session);

        if ($this->session->has(Session::DISCORD_OAUTH2_ACCESS_TOKEN)) {
            $isLoggedIn = true;

            $user = $this->getDiscordClientCurrentUser();
            $guilds = $this->getDiscordClientCurrentUserGuilds();

            $guildSelectionForm = new GuildSelectionForm($this->guard, $this->session, $guilds);

            foreach ($guilds as $guild) {
                if ($request->getAttribute('guildId') != $guild->id)
                    continue;

                $selectedGuild = $guild;
                break;
            }
        }

        $this->templateRenderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'currentPage', "Dashboard");
        $this->templateRenderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'isLoggedIn', $isLoggedIn);
        $this->templateRenderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'user', $user);
        $this->templateRenderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'guildSelectionForm', $guildSelectionForm);
        $this->templateRenderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'selectedGuild', $selectedGuild);

        return new HtmlResponse($this->templateRenderer->render('app::dashboard', [
            'layout' => 'layout::dashboard',
            'nicknameForm' => $nicknameForm,
            'commandPrefixForm' => $commandPrefixForm,
        ]));
    }
}