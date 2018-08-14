<?php

namespace App\Form;

use RestCord\Model\Guild\Guild;
use Zend\Expressive\Csrf\SessionCsrfGuard;
use Zend\Expressive\Session\SessionInterface;
use Zend\Form\Element\Select;

/**
 * Class GuildSelectionForm
 * @package App\Form
 */
class GuildSelectionForm extends CsrfGuardedForm
{
    /**
     * GuildSelectionForm constructor.
     * @param SessionCsrfGuard $guard
     * @param SessionInterface $session
     * @param Guild[] $guilds
     */
    public function __construct(SessionCsrfGuard $guard, SessionInterface $session, $guilds)
    {
        parent::__construct($guard, $session, 'csrf_guild_selection_form');

        $guildIdOptions = array();

        foreach ($guilds as $guild) {
            if ($guild->permissions & 0x20) {
                // User has manage server perms
                $guildIdOptions[$guild->id] = $guild->name;
            }
        }

        if (count($guildIdOptions) == 0) {
            $this->add([
                'name' => 'guildId',
                'type' => Select::class,
                'attributes' => [
                    'class' => 'custom-select guildId'
                ],
                'options' => [
                    'empty_option' => 'No Guild Available'
                ]
            ]);
        } else {
            $this->add([
                'name' => 'guildId',
                'type' => Select::class,
                'attributes' => [
                    'class' => 'custom-select guildId'
                ],
                'options' => [
                    'value_options' => $guildIdOptions
                ]
            ]);
        }
    }
}