<?php

namespace App\Twig;

use App\Entity\User;
use App\Enum\UserStatus;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class UserStatusBadgeExtension extends AbstractExtension
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('user_status_badge', [$this, 'getUserStatusBadge'], [
                'is_safe' => ['html']
            ]),
        ];
    }

    public function getUserStatusBadge(int $status): string
    {
        return match ($status) {
            UserStatus::BLOCKED => new Markup('<span class="badge bg-danger">' . $this->translator->trans('user.statuses.BLOCKED') . '</span>', 'UTF-8'),
            UserStatus::INACTIVE => new Markup('<span class="badge bg-gray-500">' . $this->translator->trans('user.statuses.INACTIVE') . '</span>', 'UTF-8'),
            UserStatus::ACTIVE => new Markup('<span class="badge bg-success">' . $this->translator->trans('user.statuses.ACTIVE') . '</span>', 'UTF-8'),
            default => throw new \LogicException(),
        };
    }
}
