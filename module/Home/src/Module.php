<?php

namespace Home {

    use Zend\EventManager\EventInterface;
    use Zend\ModuleManager\Feature\BootstrapListenerInterface;
    use Zend\ModuleManager\Feature\ConfigProviderInterface;
    use Zend\Mvc\ModuleRouteListener;
    use Zend\Session\Container;
    use Zend\Session\SessionManager;
    use Zend\Session\Validator\HttpUserAgent;
    use Zend\Session\Validator\RemoteAddr;

    class Module implements ConfigProviderInterface, BootstrapListenerInterface
    {
        public function getConfig()
        {
            return include __DIR__ . '/../config/module.config.php';
        }

        public function onBootstrap(EventInterface $e)
        {
            $eventManager = $e->getApplication()->getEventManager();
            $moduleRouteListener = new ModuleRouteListener();
            $moduleRouteListener->attach($eventManager);
            $this->bootstrapSession($e);
        }

        public function bootstrapSession($e)
        {
            $session = $e->getApplication()->getServiceManager()->get(SessionManager::class);
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

            $chain = $session->getValidatorChain();

            $validator = new RemoteAddr($container->remoteAddr);
            $chain->attach('session.validate', array($validator, 'isValid'));

            $validator = new HttpUserAgent($container->httpUserAgent);
            $chain->attach('session.validate', array($validator, 'isValid'));
        }
    }
}