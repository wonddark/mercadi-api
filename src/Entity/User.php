<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Repository\UserRepository;
use App\State\WhoAmI;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    normalizationContext: ["groups" => ["user:read"]],
    denormalizationContext: ["groups" => ["user:write"]]
)]
#[Get]
#[Get(
    uriTemplate: "/me",
    outputFormats: ["json"],
    provider: WhoAmI::class
)]
#[GetCollection]
#[Patch]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups([
        "user:read",
        "offer:general:read",
        "offer:post:read",
        "offer:patch:read",
        "bid:general:read",
        "bid:get:highest:read"
    ])]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    #[Groups([
        "user:read",
        "user:write",
        "offer:general:read",
        "offer:post:read",
        "offer:patch:read",
        "bid:general:read",
        "bid:get:highest:read"
    ])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups([
        "user:read",
        "user:write",
        "bid:get:highest:read",
        "offer:general:read",
        "offer:post:read",
        "offer:patch:read",
        "bid:general:read",
        "bid:get:highest:read"
    ])]
    private ?string $lastname = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["user:read"])]
    private ?Account $account = null;

    #[ORM\OneToMany(
        mappedBy: 'user',
        targetEntity: Item::class,
        orphanRemoval: true
    )]
    #[Groups(["user:read"])]
    private Collection $items;

    #[ORM\OneToMany(
        mappedBy: 'user',
        targetEntity: Bid::class,
        orphanRemoval: true
    )]
    #[Groups(["user:read"])]
    private Collection $bids;

    #[Pure]
    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->bids = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
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

    /** @noinspection PhpUnused */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /** @noinspection PhpUnused */
    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return Collection<int, Item>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /** @noinspection PhpUnused */
    public function addItem(Item $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setUser($this);
        }

        return $this;
    }

    /** @noinspection PhpUnused */
    public function removeItem(Item $item): self
    {
        if ($this->items->removeElement($item)) {
            if ($item->getUser() === $this) {
                $item->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Bid>
     * @noinspection PhpUnused
     */
    public function getBids(): Collection
    {
        return $this->bids;
    }

    /** @noinspection PhpUnused */
    public function addBid(Bid $bid): self
    {
        if (!$this->bids->contains($bid)) {
            $this->bids->add($bid);
            $bid->setUser($this);
        }

        return $this;
    }

    /** @noinspection PhpUnused */
    public function removeBid(Bid $bid): self
    {
        if ($this->bids->removeElement($bid)) {
            if ($bid->getUser() === $this) {
                $bid->setUser(null);
            }
        }

        return $this;
    }
}
