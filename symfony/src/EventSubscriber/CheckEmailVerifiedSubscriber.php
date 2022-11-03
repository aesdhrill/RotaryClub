<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Enum\UserStatus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Security\EmailUnverifiedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CredentialsInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class CheckEmailVerifiedSubscriber implements EventSubscriberInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router){
        $this->router = $router;
    }

    public function onCheckPassport(CheckPassportEvent $event)
    {
        $passport = $event->getPassport();

        $user = $passport->getUser();
        if (!$user instanceof User) {
            throw new \Exception('Unexpected user type');
        }

        if (!($user->getStatus() === UserStatus::ACTIVE)) {
            throw new EmailUnverifiedException(
                'Please verify your email.'
            );
        }
    }

    public function onLoginFailure(LoginFailureEvent $event){
        if (!$event->getException() instanceof EmailUnverifiedException){
            return;
        }

        $event->setResponse(new RedirectResponse($this->router->generate('security_activate_resend', )));
        dd($event);
    }

    public static function getSubscribedEvents()
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', -10],
            LoginFailureEvent::class => 'onLoginFailure',
        ];
        // TODO: Implement getSubscribedEvents() method.
    }
}

