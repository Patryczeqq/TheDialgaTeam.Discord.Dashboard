<?php

namespace Home\Controller
{

    use Home\Model\Discord\OAuth2;
    use Home\Model\Session;
    use Home\Model\TheDialgaTeam\Discord\DiscordBot;
    use Home\Model\TheDialgaTeam\Discord\Table\Model\DiscordAppModel;
    use Zend\Hydrator\ClassMethods;
    use Zend\Json\Json;
    use Zend\Mvc\Controller\AbstractActionController;
    use Zend\View\Model\ViewModel;

    class IndexController extends AbstractActionController
    {
        /**
         * @var Session
         */
        private $session;

        /**
         * @var DiscordBot
         */
        private $discordBot;

        public function __construct()
        {
            $this->session = new Session();
            $this->discordBot = new DiscordBot();
        }

        public function indexAction()
        {
            if (!$this->session->startOrResumeSession())
                return $this->redirect()->toRoute('home');

            $discordAppModels = $this->discordBot->discordApp->getDiscordAppModels();
            $discordAppModelsArray = array();

            foreach ($discordAppModels as $discordAppModel)
            {
                $discordAppModelsArray[] = $discordAppModel->toArray();
            }

            // Cache Response for later reuse:
            $this->session->discordAppModelsJson = Json::encode($discordAppModelsArray);


            return new ViewModel([
                'discordAppModels' => $discordAppModels,
                'csrf' => $this->session->generateNewCsrfToken(),
            ]);
        }

        public function loginAction()
        {
            if (!$this->session->startOrResumeSession())
                return $this->redirect()->toRoute('home');

            if (isset($_GET['error']))
                return $this->redirect()->toRoute('home');

            // If login button is clicked
            if ($_POST['action'] == 'login')
            {
                if (!$this->session->validateCsrfToken($_POST['csrf']))
                    return $this->redirect()->toRoute('home');

                $clientId = $_POST['clientId'];
                $clientSecret = '';

                $jsonArray = Json::decode($this->session->discordAppModelsJson, Json::TYPE_ARRAY);

                foreach ($jsonArray as $key => $value)
                {
                    /** @var DiscordAppModel $discordAppModel */
                    $discordAppModel = (new ClassMethods())->hydrate($value, new DiscordAppModel());

                    if ($discordAppModel->getClientId() != $clientId)
                        continue;

                    $clientSecret = $discordAppModel->getClientSecret();
                    break;
                }

                // Store ClientId and ClientSecret into session so that we won't forget.
                $this->session->clientId = $clientId;
                $this->session->clientSecret = $clientSecret;

                $oAuth2 = new OAuth2($clientId, $clientSecret);
                $scopes = [ $oAuth2::SCOPE_IDENTIFY, $oAuth2::SCOPE_GUILDS ];

                return $this->redirect()->toUrl($oAuth2->getAuthorizationUrl($scopes, $this->session->generateNewCsrfToken()));
            }
            else
            {
                if (!$this->session->validateCsrfToken($_GET['state']))
                    return $this->redirect()->toRoute('home');

                $code = $_GET['code'];

                $oAuth2 = new OAuth2($this->session->clientId, $this->session->clientSecret);
                $test = $oAuth2->getAccessToken($code);

                //return $this->redirect()->toRoute('dashboard');
                return new ViewModel(['test' => $this->session->clientSecret]);
            }
        }

        public function dashboardAction()
        {
            if (!$this->session->startOrResumeSession())
                return $this->redirect()->toRoute('home');

            return new ViewModel();
        }
    }
}