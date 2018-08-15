<?php

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;

class LogoutHandler extends BaseFormHandler
{
    protected function onProcess(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->session->clear();
        return new RedirectResponse($this->urlHelper->generate('home'));
    }
}