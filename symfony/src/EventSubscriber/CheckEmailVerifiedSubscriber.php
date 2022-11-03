<?php

namespace App\EventSubscriber;

use App\Entity\Token;
use App\Entity\User;
use App\Enum\TokenType;
use App\Enum\UserStatus;
use App\Manager\TokenManager;
use App\Repository\TokenRepository;
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
    private TokenRepository $tokenRepository;
    private TokenManager $tokenManager;

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
//        dd($event);
    }

    public function onLoginFailure(LoginFailureEvent $event){

        if (!$event->getException() instanceof EmailUnverifiedException){
            return;
        }


        $event->getRequest()->getSession()->clear('_security.last_error');
        /** @var User $user */
        $user = $event->getPassport()->getUser();

        $activationTokens = $user->getTokens()
            ->filter(fn(Token $x) => (in_array($x->getType(),
                                TokenType::getTokenTypesForActivation(), true) && $x->getValidTo() >= new \DateTime())
                    );

        if ($activationTokens->isEmpty()){
            $newToken = new Token();
            $newToken->setType(TokenType::ACTIVATE_ACCOUNT);
            $newToken->setValidTo(new \DateTime('+1day'));
            $newToken->setUser($user);
            $this->tokenManager->save($newToken);
        } else {
            $newToken = $activationTokens->first();
        }



        $response = new RedirectResponse($this->router->generate('security_activate_resend', ['token' => $newToken->getValue()]));
//        $response->headers->set('user',  $event->getPassport()?->getUser()->getId());
        $event->setResponse($response);
//        dd($event);

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

