<?php

namespace Home\Controller {

    use Home\Model\Discord\OAuth2;
    use Home\Model\Form\Index\IndexActionForm;
    use Home\Model\Form\Index\LoginActionForm;
    use Home\Model\TheDialgaTeam\Discord\NancyGateway;
    use Zend\Http\Request;
    use Zend\Mvc\Controller\AbstractActionController;
    use Zend\Session\Container;
    use Zend\Session\SessionManager;
    use Zend\View\Model\ViewModel;

    class IndexController extends AbstractActionController
    {
        /**
         * @var NancyGateway
         */
        private $nancyGateway;

        /**
         * @var SessionManager
         */
        private $session;

        /**
         * @var Container
         */
        private $sessionContainer;

        /**
         * @var Request
         */
        private $request;

        public function __construct()
        {
            $this->nancyGateway = new NancyGateway();
            $this->session = (new Container('initialized'))->getManager();
            $this->sessionContainer = new Container('discord_session');
            $this->request = $this->getRequest();
        }

        public function indexAction()
        {
            try {
                $discordAppTables = $this->nancyGateway->getDiscordAppTable();
            } catch (\Exception $ex) {
                $discordAppTables = array();
            }

            $form = new IndexActionForm($discordAppTables);

            return new ViewModel([
                'indexForm' => $form,
            ]);
        }

        public function loginAction()
        {
            try {
                $discordAppTables = $this->nancyGateway->getDiscordAppTable();
            } catch (\Exception $ex) {
                return $this->redirect()->toRoute('home');
            }

            if (!$this->sessionContainer->getManager()->isValid()) {
                return $this->redirect()->toRoute('home');
            }

            if (!empty($this->request->getQuery('error'))) {
                return $this->redirect()->toRoute('home');
            }

            $form = new IndexActionForm($discordAppTables);
            $form->setValidationGroup('clientId', 'action', 'loginCsrf');
            $form->setData($this->request->getPost());

            if (!$form->isValid()) {
                return $this->redirect()->toRoute('home');
            } else {
                $data = $form->getData();

                $clientId = $data['clientId'];

                foreach ($discordAppTables as $discordAppTable) {
                    if ($discordAppTable->getClientId() != $clientId) {
                        continue;
                    } else {
                        $this->sessionContainer->clientId = $discordAppTable->getClientId();
                        $this->sessionContainer->clientSecret = $discordAppTable->getClientSecret();
                        break;
                    }
                }

                $form = new LoginActionForm();
                $form->setAttributes([
                    'action' => $this->url()->fromRoute('login_authentication'),
                    'method' => 'get'
                ]);
                $form->prepare();

                $oAuth2 = new OAuth2($this->sessionContainer->clientId, $this->sessionContainer->clientSecret, $this->url()->fromRoute('login_authentication'));

                return $this->redirect()->toUrl($oAuth2->getAuthorizationUrl([$oAuth2::SCOPE_IDENTIFY, $oAuth2::SCOPE_GUILDS], $form->get('state')->getValue()));
            }
        }

        public function dashboardAction()
        {
            /*if (!$this->session->startOrResumeSession())
                return $this->redirect()->toRoute('home');

            $oAuth2 = new OAuth2($this->session->clientId, $this->session->clientSecret);

            if (!$oAuth2->isAccessTokenValid())
                $oAuth2->getNewAccessToken();

            $discordClient = new DiscordClient(['token' => $this->session->access_token]);

            return new ViewModel();*/
        }
    }
}