<?php

namespace MauticPlugin\MauticRegistrationBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Mautic\UserBundle\Entity\User;

class ConfigAccessListener implements EventSubscriberInterface
{
    private const BLOCKED_ROUTES = [
        '/s/config/edit',
        '/s/config/env',
        '/s/user/account',
    ];

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RouterInterface $router,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 10],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return;
        }

        if ($user->getRole()?->getName() !== 'HaiKe User') {
            return;
        }

        $path = $event->getRequest()->getPathInfo();
        foreach (self::BLOCKED_ROUTES as $blocked) {
            if (str_starts_with($path, $blocked)) {
                $event->setResponse(
                    new RedirectResponse(
                        $this->router->generate('mautic_dashboard_index')
                    )
                );
                return;
            }
        }
    }
}
