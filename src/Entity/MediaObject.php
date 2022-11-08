<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\MediaObjectRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MediaObjectRepository::class)]
#[ApiResource]
class MediaObject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
