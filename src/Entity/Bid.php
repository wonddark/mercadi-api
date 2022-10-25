<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Repository\BidRepository;
use App\State\RetireBid;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: BidRepository::class)]
#[ApiResource(
    normalizationContext: [
        "groups" => ["bid:general:read"]
    ],
    order: ["publishedAt" => "DESC"]
)]
#[Get]
#[GetCollection]
#[GetCollection(
    uriTemplate: "/user/{id}/bids",
    uriVariables: [
        "id" => new Link(fromClass: User::class, fromProperty: "bids")
    ]
)]
#[GetCollection(
    uriTemplate: "/offer/{id}/bids",
    uriVariables: [
        "id" => new Link(fromClass: Offer::class, fromProperty: "bids")
    ]
)]
#[Post(
    normalizationContext: [
        "groups" => ["bid:post:read"]
    ],
    denormalizationContext: [
        "groups" => ["bid:post:write"]
    ]
)]
#[Delete(
    processor: RetireBid::class
)]
class Bid
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(["offer:general:read", "bid:post:read", "bid:general:read"])]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'bids')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["offer:general:read", "bid:post:read", "bid:general:read"])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'bids')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["bid:post:write", "bid:post:read", "bid:general:read"])]
    private ?Offer $offer = null;

    #[ORM\Column]
    #[Groups([
        "offer:general:read",
        "bid:post:write",
        "bid:post:read",
        "bid:general:read"
    ])]
    private ?float $quantity = null;

    #[ORM\Column]
    #[Groups(["offer:general:read", "bid:post:read", "bid:general:read"])]
    private ?DateTimeImmutable $publishedAt;

    #[ORM\Column]
    #[Groups(["bid:post:read", "bid:general:read"])]
    private ?bool $isDeletable = true;

    public function __construct()
    {
        $this->publishedAt = new DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getOffer(): ?Offer
    {
        return $this->offer;
    }

    public function setOffer(?Offer $offer): self
    {
        $this->offer = $offer;

        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function isIsDeletable(): ?bool
    {
        return $this->isDeletable;
    }

    public function setIsDeletable(bool $isDeletable): self
    {
        $this->isDeletable = $isDeletable;

        return $this;
    }
}
