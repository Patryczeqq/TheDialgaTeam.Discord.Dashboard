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
                $discordAppModels = (new ClassMethods())->hydrate($jsonArray, new DiscordAppModel());

                /** @var DiscordAppModel $discordAppModel */
                foreach ($discordAppModels as $discordAppModel)
                {
                    if ($discordAppModel->getClientId() == $clientId)
                        $clientSecret = $discordAppModel->getClientSecret();
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

                try
                {
                    $oAuth2 = new OAuth2($this->session->clientId, $this->session->clientSecret);
                    $oAuth2->getAccessToken($code);

                    return $this->redirect()->toRoute('dashboard');
                }
                catch (\Exception $e)
                {
                    return $this->redirect()->toRoute('home');
                }
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