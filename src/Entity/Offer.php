<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\OfferRepository;
use App\Security\Voter\OfferOwnershipVoter;
use App\State\CloseOffer;
use App\State\OfferCreator;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: OfferRepository::class)]
#[ApiResource(
    normalizationContext: [
        "groups" => ["offer:general:read"]
    ],
    order: ["publishedAt" => 'DESC']
)]
#[Get]
#[GetCollection]
#[GetCollection(
    uriTemplate: "/user/{id}/offers",
    uriVariables: [
        "id" => new Link(fromClass: User::class, fromProperty: "offers")
    ]
)]
#[Post(
    normalizationContext: [
        "groups" => ["offer:post:read"]
    ],
    denormalizationContext: [
        "groups" => ["offer:post:write"]
    ],
    processor: OfferCreator::class
)]
#[Patch(
    normalizationContext: [
        "groups" => ["offer:patch:read"]
    ],
    denormalizationContext: [
        "groups" => ["offer:patch:write"]
    ],
    security: "is_granted('" . OfferOwnershipVoter::EDIT . "', object)"
)]
#[Delete(
    security: "is_granted('" . OfferOwnershipVoter::DELETE . "', object)",
    processor: CloseOffer::class
)]
class Offer
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups([
        "offer:post:read",
        "offer:patch:read",
        "offer:general:read"
    ])]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: "offers")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([
        "offer:post:read",
        "offer:patch:read",
        "offer:general:read"
    ])]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Groups([
        "offer:post:read",
        "offer:post:write",
        "offer:patch:read",
        "offer:patch:write",
        "offer:general:read"
    ])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(["offer:post:write"])]
    private ?float $initialBid = null;

    #[ORM\Column(length: 255)]
    #[Groups([
        "offer:post:read",
        "offer:post:write",
        "offer:patch:read",
        "offer:patch:write",
        "offer:general:read"
    ])]
    private ?string $description = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    #[Groups([
        "offer:post:read",
        "offer:patch:read",
        "offer:general:read"
    ])]
    private array $images = [];

    #[ORM\Column]
    #[Groups([
        "offer:post:read",
        "offer:patch:read",
        "offer:general:read"
    ])]
    private ?DateTimeImmutable $publishedAt;

    #[ORM\OneToMany(
        mappedBy: 'offer',
        targetEntity: Bid::class,
        orphanRemoval: true
    )]
    #[Groups([
        "offer:post:read",
        "offer:patch:read",
        "offer:general:read"
    ])]
    private Collection $bids;

    #[ORM\Column]
    #[Groups([
        "offer:post:read",
        "offer:patch:read",
        "offer:general:read"
    ])]
    private ?bool $isOpen = true;

    public function __construct()
    {
        $this->bids = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getInitialBid(): ?float
    {
        return $this->initialBid;
    }

    public function setInitialBid(float $initialBid): self
    {
        $this->initialBid = $initialBid;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function setImages(?array $images): self
    {
        $this->images = $images;

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

    /**
     * @return Collection<int, Bid>
     */
    public function getBids(): Collection
    {
        return $this->bids;
    }

    public function addBid(Bid $bid): self
    {
        if (!$this->bids->contains($bid)) {
            $this->bids->add($bid);
            $bid->setOffer($this);
        }

        return $this;
    }

    public function removeBid(Bid $bid): self
    {
        if ($this->bids->removeElement($bid)) {
            // set the owning side to null (unless already changed)
            if ($bid->getOffer() === $this) {
                $bid->setOffer(null);
            }
        }

        return $this;
    }

    public function isIsOpen(): ?bool
    {
        return $this->isOpen;
    }

    public function setIsOpen(bool $isOpen): self
    {
        $this->isOpen = $isOpen;

        return $this;
    }
}
