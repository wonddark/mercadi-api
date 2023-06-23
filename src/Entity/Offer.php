<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Repository\OfferRepository;
use App\Security\Voter\OfferVoter;
use App\State\OfferCreator;
use App\State\OfferRetractor;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: OfferRepository::class)]
#[ApiResource(order: ["quantity" => "DESC"])]
#[GetCollection(
    uriTemplate: "/bidding/{id}/offers",
    uriVariables: ["id" => new Link(fromProperty: "offers", fromClass: Bidding::class)],
    normalizationContext: ["groups" => ["bidding:offer:read"]],
    security: "is_granted('" . OfferVoter::VIEW . "', object)",
)]
#[GetCollection(
    uriTemplate: "/user/{id}/offers",
    uriVariables: ["id" => new Link(fromProperty: "offers", fromClass: User::class)],
    normalizationContext: ["groups" => ["user:offer:read"]],
    security: "is_granted('" . OfferVoter::VIEW . "', object)",
)]
#[Post(
    uriTemplate: "/bidding/push",
    normalizationContext: ["groups" => ["bidding:offer:push:read"]],
    denormalizationContext: ["groups" => ["bidding:offer:push:write"]],
    security: "is_granted('" . OfferVoter::EDIT . "', object)",
    processor: OfferCreator::class
)]
#[Delete(
    uriTemplate: "/bidding/remove/{id}",
    normalizationContext: [],
    denormalizationContext: [],
    security: "is_granted('" . OfferVoter::EDIT . "', object)",
    processor: OfferRetractor::class
)]
class Offer
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups([
        "bidding:offer:read",
        "user:offer:read",
        "bidding:offer:push:read"
    ])]
    private ?Uuid $id = null;

    #[ORM\Column]
    #[Groups([
        "bidding:offer:read",
        "user:offer:read",
        "bidding:offer:push:read",
        "bidding:offer:push:write"
    ])]
    private ?float $quantity = 0;

    #[ORM\Column]
    #[Groups([
        "bidding:offer:read",
        "user:offer:read",
        "bidding:offer:push:read"
    ])]
    private ?DateTimeImmutable $publishedAt;

    #[ORM\Column]
    #[Groups([
        "bidding:offer:read",
        "user:offer:read",
        "bidding:offer:push:read"
    ])]
    private ?bool $deletable = false;

    #[ORM\ManyToOne(inversedBy: 'offers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([
        "bidding:offer:read"
    ])]
    private ?User $user = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'], inversedBy: 'offers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([
        "user:offer:read",
        "bidding:offer:push:read",
        "bidding:offer:push:write"
    ])]
    private ?Bidding $bids = null;

    public function __construct()
    {
        $this->publishedAt = new DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getBids(): ?Bidding
    {
        return $this->bids;
    }

    public function setBids(?Bidding $bids): self
    {
        $this->bids = $bids;

        return $this;
    }
}
