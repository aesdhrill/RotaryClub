<?php

namespace App\Repository;

//use App\Entity\Token;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(
        ManagerRegistry $registry,
        private UrlGeneratorInterface $router,
        protected TranslatorInterface $translator,
    ) {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    #[ArrayShape([
        'data' => "array",
        'recordsTotal' => "int",
        'recordsFiltered' => "int",
        'draw' => "int",
    ])]
    public function findForDt(Request $request, ?User $currentUser): array
    {
        $offset = $request->get('start');
        $orders = $request->get('order');
        $columns = $request->get('columns');
        $search = strtolower($request->get('search')['value']);

        $qb = $this->createQueryBuilder('u');
        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->like('LOWER(u.name)', ':search'),
                $qb->expr()->like('LOWER(u.surname)', ':search'),
                $qb->expr()->like('CONCAT(LOWER(u.name), \' \', LOWER(u.surname))', ':search'),
            )
        )->setParameter('search', '%'.$search.'%');

        if ($currentUser) {
            # TODO: add more complex rules for showing users
            $allCount = $this->count([]);
//            $qb->andWhere($qb->expr()->in('p.facility', ':facilities'))
//                ->setParameter('facilities', $user->getFacilities());
//            $allCount = $this->createQueryBuilder('p')->select('count(p)')
//                ->where($qb->expr()->in('p.facility', ':facilities'))
//                ->setParameter('facilities', $user->getFacilities())
//                ->getQuery()->getSingleScalarResult();
        } else {
            $allCount = $this->count([]);
        }

        $count = $qb->select('count(u)')
            ->getQuery()
            ->getSingleScalarResult();

        $qb->select('u')
            ->setFirstResult($offset)
            ->setMaxResults($request->get('length'));

        foreach (($orders ?? []) as $order) {
            $orderCol = $columns[$order['column']]['data'];
            $orderDir = $order['dir'];

            # TODO: hack, change to virtual fullname column in User and delete this
            if ($orderCol === 'fullname') {
                $orderCol = 'name';
            }

            $qb->addOrderBy('LOWER(u.'.$orderCol.')', $orderDir);
        }

        $users = $qb->select('u')
            ->getQuery()
            ->getResult();

        $data = [];

        /** @var User $user */
        foreach ($users as $index => $user) {
            $userId = $user->getId();
            $data[] = [
                # TODO: show roles
                'index' => $offset + $index + 1,
                'fullname' => $user->getFullname(),
                'email' => $user->getEmail(),
                'roles' => array_map(
                    fn ($r) => $this->translator->trans('user.roles.'.$r),
                    array_filter($user->getRoles(), fn ($r) => $r !== 'ROLE_USER')
                ),
                'status' => $user->getStatus(),
                'actionPaths' => [
                    'show' => $this->router->generate('admin_users_details', ['id' => $userId])
                ]
            ];
        }

        return [
            'data' => $data,
            'recordsTotal' => $allCount,
            'recordsFiltered' => $count,
            'draw' => $request->get('draw'),
        ];
    }

}
