<?php

namespace Home {

    use Zend\ModuleManager\Feature\ConfigProviderInterface;
    use Zend\Mvc\ModuleRouteListener;
    use Zend\Session\SessionManager;

    class Module implements ConfigProviderInterface
    {
        public function getConfig()
        {
            return include __DIR__ . '/../config/module.config.php';
        }

        public function onBootstrap($e)
        {
            $eventManager = $e->getApplication()->getEventManager();
            $moduleRouteListener = new ModuleRouteListener();
            $moduleRouteListener->attach($eventManager);
            $this->bootstrapSession($e);
        }

        public function bootstrapSession($e)
        {
            $session = $e->getApplication()
                ->getServiceManager()
                ->get(SessionManager::class);
            $session->start();

            $container = new Container('initialized');

            if (isset($container->init)) {
                return;
            }

            $serviceManager = $e->getApplication()->getServiceManager();
            $request = $serviceManager->get('Request');

            $session->regenerateId(true);
            $container->init = 1;
            $container->remoteAddr = $request->getServer()->get('REMOTE_ADDR');
            $container->httpUserAgent = $request->getServer()->get('HTTP_USER_AGENT');

            $config = $serviceManager->get('Config');
            if (!isset($config['session'])) {
                return;
            }

            $sessionConfig = $config['session'];

            if (!isset($sessionConfig['validators'])) {
                return;
            }

            $chain = $session->getValidatorChain();

            foreach ($sessionConfig['validators'] as $validator) {
                switch ($validator) {
                    case Validator\HttpUserAgent::class:
                        $validator = new $validator($container->httpUserAgent);
                        break;
                    case Validator\RemoteAddr::class:
                        $validator = new $validator($container->remoteAddr);
                        break;
                    default:
                        $validator = new $validator();
                }

                $chain->attach('session.validate', array($validator, 'isValid'));
            }
        }
    }
}