<?php

namespace App\Form\Home;

use App\Form\CsrfGuardedForm;
use App\TheDialgaTeam\Discord\Table\DiscordAppTable;
use Zend\Expressive\Csrf\SessionCsrfGuard;
use Zend\Expressive\Session\SessionInterface;
use Zend\Form\Element\Button;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Select;

/**
 * Class BotSelectionForm
 * @package App\Form\Home
 */
class BotSelectionForm extends CsrfGuardedForm
{
    /**
     * BotSelectionForm constructor.
     * @param SessionCsrfGuard $guard
     * @param SessionInterface $session
     * @param DiscordAppTable[] $discordAppTables
     */
    public function __construct(SessionCsrfGuard $guard, SessionInterface $session, $discordAppTables = array())
    {
        parent::__construct($guard, $session, 'csrf_bot_selection_form');

        $clientIdOptions = array();

        foreach ($discordAppTables as $discordAppTable) {
            $clientIdOptions[$discordAppTable->getClientId()] = $discordAppTable->getAppName();
        }

        if (count($clientIdOptions) == 0) {
            $this->add([
                'name' => 'clientId',
                'type' => Select::class,
                'attributes' => [
                    'class' => 'custom-select'
                ],
                'options' => [
                    'empty_option' => 'No bot instance available (Try again later)'
                ]
            ]);

            $this->add([
                'name' => 'login',
                'type' => Button::class,
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'style' => 'color: white',
                    'type' => 'submit',
                    'disabled' => 'disabled',
                    'value' => 'submit'
                ],
                'options' => [
                    'label' => 'Login with Discord'
                ]
            ]);
        } else {
            $this->add([
                'name' => 'clientId',
                'type' => Select::class,
                'attributes' => [
                    'class' => 'custom-select'
                ],
                'options' => [
                    'value_options' => $clientIdOptions
                ]
            ]);

            $this->add([
                'name' => 'login',
                'type' => Button::class,
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'style' => 'color: white',
                    'type' => 'submit'
                ],
                'options' => [
                    'label' => 'Login with Discord'
                ]
            ]);
        }

        $this->add([
            'name' => 'action',
            'type' => Hidden::class,
            'attributes' => [
                'value' => 'discordAppAuthentication'
            ]
        ]);
    }
}