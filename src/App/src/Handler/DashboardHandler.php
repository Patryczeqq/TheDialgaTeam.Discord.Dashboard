<?php

namespace App\Handler;

use App\Constant\Session;
use App\Form\GuildSelectionForm;
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
        // Return vars
        $isLoggedIn = false;
        $guildSelectionForm = null;
        $user = null;

        if ($this->session->has(Session::DISCORD_OAUTH2_ACCESS_TOKEN)) {
            $isLoggedIn = true;

            $guilds = $this->getDiscordClient()->user->getCurrentUserGuilds(array());
            $user = $this->getDiscordClient()->user->getCurrentUser(array());

            $guildSelectionForm = new GuildSelectionForm($this->guard, $this->session, $guilds);
        }

        $this->templateRenderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'isLoggedIn', $isLoggedIn);
        $this->templateRenderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'user', $user);
        $this->templateRenderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'guildSelectionForm', $guildSelectionForm);

        return new HtmlResponse($this->templateRenderer->render('app::dashboard', [
            'layout' => 'layout::dashboard'
        ]));
    }
}