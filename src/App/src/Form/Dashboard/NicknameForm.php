<?php

namespace App\Form\Dashboard;

use App\Form\CsrfGuardedForm;
use App\Form\Element\Text;
use Zend\Expressive\Csrf\SessionCsrfGuard;
use Zend\Expressive\Session\SessionInterface;
use Zend\Form\Element\Button;

class NicknameForm extends CsrfGuardedForm
{
    public function __construct(SessionCsrfGuard $guard, SessionInterface $session)
    {
        parent::__construct($guard, $session, "nicknameCsrf");

        $this->add([
            'name' => 'nickname',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control'
            ],
            'options' => [
                'maxInputLength' => 32
            ]
        ]);

        $this->add([
            'name' => 'updateNickname',
            'type' => Button::class,
            'attributes' => [
                'class' => 'btn btn-outline-primary'
            ],
        ]);
    }
}