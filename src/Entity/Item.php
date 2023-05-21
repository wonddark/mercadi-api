<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\ItemRepository;
use App\Security\Voter\ItemOwnershipVoter;
use App\State\CloseBid;
use App\State\GetItemsFiltered;
use App\State\ItemCreator;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
#[ApiResource(
    normalizationContext: [
        "groups" => ["item:general:read"]
    ],
    order: ["publishedAt" => 'DESC', "bids.publishedAt" => "DESC"],
    paginationClientItemsPerPage: true
)]
#[Get]
#[GetCollection(provider: GetItemsFiltered::class)]
#[GetCollection(
    uriTemplate: "/user/{id}/items",
    uriVariables: [
        "id" => new Link(fromProperty: "items", fromClass: User::class)
    ]
)]
#[Post(
    normalizationContext: [
        "groups" => ["item:post:read"]
    ],
    denormalizationContext: [
        "groups" => ["item:post:write"]
    ],
    processor: ItemCreator::class
)]
#[Patch(
    normalizationContext: [
        "groups" => ["item:patch:read"]
    ],
    denormalizationContext: [
        "groups" => ["item:patch:write"]
    ],
    security: "is_granted('" . ItemOwnershipVoter::EDIT . "', object)"
)]
#[Delete(
    security: "is_granted('" . ItemOwnershipVoter::DELETE . "', object)",
    processor: CloseBid::class
)]
#[ApiFilter(BooleanFilter::class, properties: ["open"])]
#[ApiFilter(SearchFilter::class, properties: ["name" => 'partial', "description" => 'partial'])]
class Item
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups([
        "item:post:read",
        "item:patch:read",
        "item:general:read",
        "bid:general:read"
    ])]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: "items")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([
        "item:post:read",
        "item:patch:read",
        "item:general:read"
    ])]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups([
        "item:post:read",
        "item:post:write",
        "item:patch:read",
        "item:patch:write",
        "item:general:read",
        "bid:general:read"
    ])]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\GreaterThan(49)]
    #[Assert\LessThan(1_000_001)]
    #[Groups(["item:post:write"])]
    private ?float $initialBid = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 70)]
    #[Groups([
        "item:post:read",
        "item:post:write",
        "item:patch:read",
        "item:patch:write",
        "item:general:read"
    ])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups([
        "item:post:read",
        "item:patch:read",
        "item:general:read"
    ])]
    private ?DateTimeImmutable $publishedAt;

    #[ORM\OneToMany(
        mappedBy: 'item',
        targetEntity: Bid::class,
        orphanRemoval: true
    )]
    #[Groups([
        "item:post:read",
        "item:patch:read",
        "item:general:read"
    ])]
    private Collection $bids;

    #[ORM\Column]
    #[Groups([
        "item:post:read",
        "item:patch:read",
        "item:general:read",
        "bid:general:read"
    ])]
    private ?bool $open = true;

    #[ORM\OneToMany(mappedBy: 'item', targetEntity: MediaObject::class, orphanRemoval: true)]
    #[Groups([
        "item:post:read",
        "item:patch:read",
        "item:general:read"
    ])]
    private Collection $medias;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        "item:post:read",
        "item:patch:read",
        "item:general:read"
    ])]
    private ?Bid $highestBid = null;

    public function __construct()
    {
        $this->bids = new ArrayCollection();
        $this->publishedAt = new DateTimeImmutable();
        $this->medias = new ArrayCollection();
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
            $bid->setItem($this);
        }

        return $this;
    }

    public function removeBid(Bid $bid): self
    {
        if ($this->bids->removeElement($bid)) {
            // set the owning side to null (unless already changed)
            if ($bid->getItem() === $this) {
                $bid->setItem(null);
            }
        }

        return $this;
    }

    public function isOpen(): ?bool
    {
        return $this->open;
    }

    public function setOpen(bool $open): self
    {
        $this->open = $open;

        return $this;
    }

    /**
     * @return Collection<int, MediaObject>
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addMedia(MediaObject $media): self
    {
        if (!$this->medias->contains($media)) {
            $this->medias->add($media);
            $media->setItem($this);
        }

        return $this;
    }

    public function removeMedia(MediaObject $media): self
    {
        if ($this->medias->removeElement($media)) {
            // set the owning side to null (unless already changed)
            if ($media->getItem() === $this) {
                $media->setItem(null);
            }
        }

        return $this;
    }

    public function getHighestBid(): ?Bid
    {
        return $this->highestBid;
    }

    public function setHighestBid(Bid $highestBid): self
    {
        $this->highestBid = $highestBid;

        return $this;
    }
}
