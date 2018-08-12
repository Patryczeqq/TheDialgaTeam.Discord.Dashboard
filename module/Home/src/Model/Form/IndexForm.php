<?php

namespace Home\Model\Form;

use Home\Model\TheDialgaTeam\Discord\Table\DiscordAppTable;
use Zend\Form\Element;
use Zend\Form\Form;

class IndexForm
{
    /**
     * @var Element\Select
     */
    private $clientIdSelect;

    /**
     * @var Element\Hidden
     */
    private $actionHidden;

    /**
     * @var Element\Csrf
     */
    private $loginCsrf;

    /**
     * @var Element\Button
     */
    private $loginButton;

    /**
     * @var Form
     */
    private $form;

    /**
     * IndexForm constructor.
     * @param DiscordAppTable[] $discordAppTables
     */
    public function __construct($discordAppTables)
    {
        $this->clientIdSelect = new Element\Select('clientId');
        $this->clientIdSelect->setAttribute("class", "custom-select");

        $options = array();

        /** @var DiscordAppTable $discordAppTable */
        foreach ($discordAppTables as $discordAppTable) {
            $options[$discordAppTable->getClientId()] = $discordAppTable->getAppName();
        }

        $this->clientIdSelect->setEmptyOption("No bot instance available (Try again later)");
        $this->clientIdSelect->setValueOptions($options);

        $this->actionHidden = new Element\Hidden('action');
        $this->actionHidden->setValue("login");

        $this->loginCsrf = new Element\Csrf('loginCsrf');

        $this->loginButton = new Element\Button('login');
        $this->loginButton->setLabel("Login with Discord");
        $this->loginButton->setAttribute("class", "btn btn-primary");
        $this->loginButton->setAttribute("style", "color: white;");

        if (count($options) == 0) {
            $this->loginButton->setAttribute("disabled", "true");
        }

        $this->form = new Form('login');
        $this->form->add($this->clientIdSelect);
        $this->form->add($this->actionHidden);
        $this->form->add($this->loginCsrf);
        $this->form->add($this->loginButton);
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }
}