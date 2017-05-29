<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Routing\Router;
use App\Entity\User;

class ResponseListener implements EventSubscriberInterface
{
    private $authorizationChecker;
    private $tokenStorage;
    private $twig;
    private $router;
    private $params = [];

    public function __construct(
        $maintenance,
        AuthorizationChecker $authorizationChecker,
        TokenStorage $tokenStorage,
        Router $router,
        \Twig_Environment $twig
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->twig = $twig;
        $this->params['maintenance'] = $maintenance;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        /** @var TokenInterface $token */
        $token = $this->tokenStorage->getToken();

        if ($token !== null) {
            /** @var User $user */
            $user = is_object($token->getUser()) ? $token->getUser() : null;

            if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
                if ($this->params['maintenance']) {
                    echo $this->twig->loadTemplate('AppBundle:Exception:maintenance.html.twig')->render([]);
                    exit;
                }

                if ($user && $this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
                    if ($user->getStatus() === User::DISABLED) {
                        $request->getSession()->invalidate();
                        $this->tokenStorage->setToken(null);
                    }
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }
}
