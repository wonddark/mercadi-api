<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\ItemRepository;
use App\Security\Voter\ItemOwnershipVoter;
use App\State\ItemCreator;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
#[ApiResource(
    normalizationContext: [
        "groups" => ["item:general:read"]
    ],
    order: ["publishedAt" => 'DESC'],
    paginationClientItemsPerPage: true
)]
#[Get]
#[GetCollection]
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
#[ApiFilter(SearchFilter::class, properties: ["description" => 'partial'])]
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
        "user:offer:read"
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
        "user:offer:read"
    ])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups([
        "item:post:read",
        "item:patch:read",
        "item:general:read"
    ])]
    private ?DateTimeImmutable $publishedAt;

    #[ORM\OneToMany(mappedBy: 'item', targetEntity: MediaObject::class, orphanRemoval: true)]
    #[Groups([
        "item:post:read",
        "item:patch:read",
        "item:general:read"
    ])]
    private Collection $medias;

    #[ORM\OneToOne(mappedBy: 'item', cascade: ['persist', 'remove'])]
    #[Groups([
        "item:post:read",
        "item:patch:read",
        "item:general:read"
    ])]
    private ?Bidding $bids = null;

    #[ORM\Column]
    #[Groups([
        "item:post:read",
        "item:post:write",
        "item:patch:read",
        "item:patch:write",
        "item:general:read"
    ])]
    private float $price = 0;

    #[ORM\Column]
    #[Groups([
        "item:post:read",
        "item:post:write",
        "item:patch:read",
        "item:patch:write",
        "item:general:read"
    ])]
    private bool $bidding = true;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    #[Groups([
        "item:post:read",
        "item:post:write",
        "item:patch:read",
        "item:patch:write",
        "item:general:read"
    ])]
    private array $contactPhones = [];

    #[ORM\Column(type: Types::TEXT)]
    #[Groups([
        "item:post:read",
        "item:post:write",
        "item:patch:read",
        "item:patch:write",
        "item:general:read"
    ])]
    private ?string $additionalInfo = "";

    /*
     * Home delivery values as small int
     * 0 => No home delivery
     * 1 => Home delivery included in the price
     * 2 => Home delivery with additional cost
     */
    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups([
        "item:post:read",
        "item:post:write",
        "item:patch:read",
        "item:patch:write",
        "item:general:read"
    ])]
    private ?int $homeDelivery = 0;

    public function __construct()
    {
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

    public function getBids(): ?Bidding
    {
        return $this->bids;
    }

    public function setBids(Bidding $bids): self
    {
        // set the owning side of the relation if necessary
        if ($bids->getItem() !== $this) {
            $bids->setItem($this);
        }

        $this->bids = $bids;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function isBidding(): ?bool
    {
        return $this->bidding;
    }

    public function setBidding(bool $bidding): self
    {
        $this->bidding = $bidding;

        return $this;
    }

    public function getContactPhones(): array
    {
        return $this->contactPhones;
    }

    public function setContactPhones(array $contactPhones): self
    {
        $this->contactPhones = $contactPhones;

        return $this;
    }

    public function getAdditionalInfo(): ?string
    {
        return $this->additionalInfo;
    }

    public function setAdditionalInfo(string $additionalInfo): self
    {
        $this->additionalInfo = $additionalInfo;

        return $this;
    }

    public function getHomeDelivery(): ?int
    {
        return $this->homeDelivery;
    }

    public function setHomeDelivery(int $homeDelivery): self
    {
        $this->homeDelivery = $homeDelivery;

        return $this;
    }
}
