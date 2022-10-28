<?php

namespace App\Entity\Trait;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait CreatedAtTrait
{
    #[ORM\Column(
        type: 'datetime',
        insertable: false,
        updatable: false,
        options: [
            'virtual' => true,
            'join' => "INNER JOIN ext_log_entries le ON le.object_class='%FQCN%' AND cast(le.object_id as integer)=%TABLENAME%.id AND le.action='create'"
        ],
        columnDefinition: 'le.loggedAt'
    )]
    private readonly DateTime $createdAt;

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}