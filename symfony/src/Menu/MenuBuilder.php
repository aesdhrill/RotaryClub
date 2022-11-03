<?php

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Matcher;
use Symfony\Component\Security\Core\Security;

class MenuBuilder
{
    public function __construct(
        private FactoryInterface $factory,
        private Security $security,
        private Matcher $matcher,
    ) {}

    public function mainMenu(array $options): ItemInterface
    {
        $linkAttributes = [
            'class' => 'nav-link',
        ];

        $dropdownAttributes = [
            'class' => 'nav-link collapsed',
            'data-bs-toggle' => 'collapse',
            'aria-expanded' => true,
        ];

        $menu = $this->factory->createItem('root');

        $menu->addChild('menu.dashboard', [
            'route' => 'dashboard_index',
            'extras' => [
                'icon' => 'fas fa-home',
            ],
        ]);

//        if ($this->security->isGranted('ROLE_ADMINISTRATION')){
            $menu->addChild('menu.admin', [
                'uri' => '#',
                'extras' => [
                    'icon' => 'fas fa-users-cog',
                ],
            ]);


            $menu['menu.admin']->addChild('menu.admin_users', [
                'route' => 'admin_users_list',
                'extras' => [
                    'icon' => 'fas fa-users'
                ]
            ]);
//        }


//
//        $menu['menu.admin']->addChild('menu.facility', [
//            'route' => 'admin_facilities_list',
//            'extras' => [
//                'icon' => 'fas fa-hospitals'
//            ]
//        ]);



        $menu->addChild('menu.settings', [
            'route' => 'account_settings',
            'extras' => [
                'icon' => 'fas fa-cog',
            ],
        ]);

        foreach ($menu->getChildren() as $child) {
            if ($child->hasChildren()) {
                $child->setLinkAttributes(array_merge(
                    $dropdownAttributes,
                    [
                        'data-bs-target' => '#'. str_replace('.', '-', $child->getName()),
                    ]
                ));
            } else {
                $childLinkAttributes = $linkAttributes;
                if ($this->matcher->isCurrent($child)) {
                    $childLinkAttributes['class'] .= ' current';
                }

                $child->setLinkAttributes($childLinkAttributes);
            }
        }

        return $menu;
    }

    private function addDivider($menu): void
    {
        $menu->addChild('<hr id="'.uniqid().'">', [
            'extras' => [
                'safe_label' => true,
                'translation_domain' => false,
            ],
        ]);
    }
}
