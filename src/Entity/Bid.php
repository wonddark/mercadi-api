<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Repository\BidRepository;
use App\State\BidCreator;
use App\State\GetHighestBidPerOffer;
use App\State\GetUserBids;
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
    order: ["quantity" => "DESC"],
    paginationClientItemsPerPage: true
)]
#[Get]
#[Get(
    uriTemplate: "/offer/{id}/bids/highest",
    normalizationContext: [
        "groups" => ["bid:get:highest:read"]
    ],
    provider: GetHighestBidPerOffer::class,
)]
#[GetCollection(
    uriTemplate: "/user/{id}/bids",
    uriVariables: [
        "id" => new Link(fromClass: User::class, fromProperty: "bids"),
    ],
    filters: [
        "offer.open_filter"
    ],
    provider: GetUserBids::class
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
    ],
    processor: BidCreator::class
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
    #[Groups(["offer:general:read", "bid:post:read", "bid:general:read", "bid:get:highest:read"])]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'bids')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["offer:general:read", "bid:post:read", "bid:general:read", "bid:get:highest:read"])]
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
        "bid:general:read",
        "bid:get:highest:read"
    ])]
    private ?float $quantity = null;

    #[ORM\Column]
    #[Groups(["offer:general:read", "bid:post:read", "bid:general:read", "bid:get:highest:read"])]
    private ?DateTimeImmutable $publishedAt;

    #[ORM\Column]
    #[Groups(["bid:post:read", "bid:general:read"])]
    private ?bool $deletable = true;

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

    public function isDeletable(): ?bool
    {
        return $this->deletable;
    }

    public function setDeletable(bool $deletable): self
    {
        $this->deletable = $deletable;

        return $this;
    }
}
