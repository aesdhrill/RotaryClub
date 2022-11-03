<?php

namespace App\Service;

use App\Entity\Token;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailSender
{
    private string $transportName = 'main';

    public function __construct(
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
//        private UserRepository $userRepository,
        private string $uploadDirectory,
        private string $assetDirectory,
        private array $transports,
    ) {}

    public function send(
        string  $subject,
        ?array  $to = [],
        ?array  $cc = [],
        ?array  $bcc = [],
        ?array  $replyTo = [],
        ?string $text = '',
        ?string $html = '',
        ?string $htmlTemplate = '',
        ?array  $templateContext = [],
        ?array  $uploadedFiles = [],
        ?array  $assetFiles = [],
    ): void {
        if (!$to && !$cc && !$bcc) {
            throw new \LogicException('Email cannot have empty recipients!');
        }

        if (!$text && !$html && !$htmlTemplate) {
            throw new \LogicException('Email cannot have empty body!');
        }

        $emails = [];

        $users = [
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'replyTo' => $replyTo
        ];

        foreach ($users as $group => $userGroup) {
            $emailsGroup = [];
            /** @var User $user */
            foreach ($userGroup as $user) {
                if ($user instanceof User) {
                    $emailsGroup[] = $user->getEmail();
                } elseif (is_string($user)) {
                    $emailsGroup[] = $user;
                }
//                if ($user->getStatus() !== UserStatus::BLOCKED) {
//                }
            }
            $emails[$group] = $emailsGroup;
        }

        $senderAddress = Dsn::fromString($this->transports[$this->transportName])->getUser();

        if ($htmlTemplate) {
            $email = (new TemplatedEmail())
                ->htmlTemplate($htmlTemplate)
                ->context($templateContext);
        } else {
            $email = (new Email())
                ->text($text)
                ->html($html);
        }

        $email = $email->from(new Address($senderAddress, $this->translator->trans('project.acronym')))
            ->to(...$emails['to'])
            ->cc(...$emails['cc'])
            ->bcc(...$emails['bcc'])
            ->replyTo(...$emails['replyTo'])
            ->subject($subject);

        foreach ($uploadedFiles as $file) {
            $email->attachFromPath($this->uploadDirectory . $file);
        }

        foreach ($assetFiles as $file) {
            $email->attachFromPath($this->assetDirectory . $file);
        }

        $email->getHeaders()->addTextHeader('X-Transport', $this->transportName);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            if ($_ENV['APP_ENV'] === 'dev') {
                dump($e);
            }
        }
    }

    public function sendResetPassword(User $user, Token $token): void
    {
        $this->setTransport('tech');
        $this->send(
            $this->translator->trans('project.acronym') . ' - ' . $this->translator->trans('emails.titles.reset_password'),
            [$user],
            htmlTemplate: 'emails/' . $this->translator->getLocale() . '/reset_password.html.twig',
            templateContext: [
                'token' => $token->getValue(),
//                'person' => $person,
            ]
        );
    }

    public function sendSignUp(User $user, Token $token): void
    {
        dump('in mailer');

        dump($user, $token);
//        $this->setTransport('tech');
//        $this->send(
//            $this->translator->trans('project.acronym') . ' - ' . $this->translator->trans('emails.titles.activate_account'),
//            [$user],
//            htmlTemplate: 'emails/' . $this->translator->getLocale() . '/activate_account.html.twig',
//            templateContext: [
//                'token' => $token->getValue(),
//            ]
//        );
    }

    public function setTransport(string $transportName): MailSender
    {
        $this->transportName = $transportName;

        return $this;
    }
}
