<?php

namespace Home\Model\Form\Index;

use Home\Model\TheDialgaTeam\Discord\Table\DiscordAppTable;
use Zend\Form\Element;
use Zend\Form\Form;

/**
 * Class IndexActionForm
 * @package Home\Model\Form\Index
 */
class IndexActionForm extends Form
{
    /**
     * IndexForm constructor.
     * @param DiscordAppTable[] $discordAppTables
     */
    public function __construct($discordAppTables = array())
    {
        parent::__construct();

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
                    'disabled' => 'true'
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
                    'type' => 'submit',
                    'disabled' => 'false'
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
                'value' => 'login'
            ]
        ]);

        $this->add([
            'name' => 'loginCsrf',
            'type' => Element\Csrf::class,
            'options' => [
                'csrf_options' => [
                    'timeout' => 600
                ]
            ]
        ]);
    }
}