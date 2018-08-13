<?php

namespace App\Form;

use App\TheDialgaTeam\Discord\Table\DiscordAppTable;
use Zend\Expressive\Csrf\SessionCsrfGuard;
use Zend\Form\Element;
use Zend\Form\Form;

/**
 * Class HomeHandlerForm
 * @package App\Form
 */
class HomeHandlerForm extends Form
{
    /**
     * @var SessionCsrfGuard
     */
    private $guard;

    /**
     * HomeHandlerForm constructor.
     * @param SessionCsrfGuard $guard
     * @param DiscordAppTable[] $discordAppTables
     */
    public function __construct(SessionCsrfGuard $guard, $discordAppTables = array())
    {
        parent::__construct();

        $this->guard = $guard;

        $clientIdOptions = array();

        foreach ($discordAppTables as $discordAppTable) {
            $clientIdOptions[$discordAppTable->getClientId()] = $discordAppTable->getAppName();
        }

        if (count($clientIdOptions) == 0) {
            $this->add([
                'name' => 'clientId',
                'type' => Element\Select::class,
                'attributes' => [
                    'class' => 'custom-select'
                ],
                'options' => [
                    'empty_option' => 'No bot instance available (Try again later)'
                ]
            ]);

            $this->add([
                'name' => 'login',
                'type' => Element\Button::class,
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'style' => 'color: white',
                    'type' => 'submit',
                    'disabled' => 'disabled'
                ],
                'options' => [
                    'label' => 'Login with Discord'
                ]
            ]);
        } else {
            $this->add([
                'name' => 'clientId',
                'type' => Element\Select::class,
                'attributes' => [
                    'class' => 'custom-select'
                ],
                'options' => [
                    'value_options' => $clientIdOptions
                ]
            ]);

            $this->add([
                'name' => 'login',
                'type' => Element\Button::class,
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
            'type' => Element\Hidden::class,
            'attributes' => [
                'value' => 'discordAppAuthentication'
            ]
        ]);

        $this->add([
            'name' => 'csrf',
            'type' => Element\Hidden::class
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'csrf',
                'required' => true,
                'validators' => [
                    [
                        'name' => 'callback',
                        'options' => [
                            'callback' => function ($value) {
                                return $this->guard->validateToken($value);
                            },
                            'messages' => [
                                'callbackValue' => 'The form submitted did not originate from the expected site'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}