<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Patch;
use App\Repository\BiddingRepository;
use App\Security\Voter\BiddingVoter;
use App\State\BiddingCloser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: BiddingRepository::class)]
#[ApiResource(
    normalizationContext: [
        "groups" => ["bidding:read"]
    ]
)]
#[Patch(
    uriTemplate: "/bidding/{id}/switch",
    denormalizationContext: ["groups" => ["bidding:close"]],
    security: "is_granted('" . BiddingVoter::EDIT_OPEN_STATE . "', object)",
    processor: BiddingCloser::class
)]
class Bidding
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(
        ["bidding:read", "item:general:read", "user:offer:read"]
    )]
    private ?Uuid $id = null;

    #[ORM\OneToOne(inversedBy: 'bids', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(
        ["bidding:read", "user:offer:read"]
    )]
    private ?Item $item = null;

    #[ORM\OneToMany(mappedBy: 'bids', targetEntity: Offer::class, orphanRemoval: true)]
    private Collection $offers;

    #[ORM\Column]
    #[Groups([
        "bidding:read",
        "item:general:read",
        "bidding:close",
        "user:offer:read"
    ])]
    private bool $open = true;

    #[ORM\Column]
    #[Groups(
        ["bidding:read", "item:general:read"]
    )]
    private float $highestOffer = 0;

    #[ORM\Column]
    #[Groups(
        ["bidding:read", "item:general:read"]
    )]
    private ?int $totalOffers = 0;

    public function __construct()
    {
        $this->offers = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    /**
     * @return Collection<int, Offer>
     */
    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function addOffer(Offer $offer): self
    {
        if (!$this->offers->contains($offer)) {
            $this->offers->add($offer);
            $offer->setBids($this);
        }

        return $this;
    }

    public function removeOffer(Offer $offer): self
    {
        if ($this->offers->removeElement($offer)) {
            // set the owning side to null (unless already changed)
            if ($offer->getBids() === $this) {
                $offer->setBids(null);
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

    public function getHighestOffer(): ?float
    {
        return $this->highestOffer;
    }

    public function setHighestOffer(float $highestOffer): self
    {
        $this->highestOffer = $highestOffer;

        return $this;
    }

    public function getTotalOffers(): ?int
    {
        return $this->totalOffers;
    }

    public function setTotalOffers(int $totalOffers): self
    {
        $this->totalOffers = $totalOffers;

        return $this;
    }
}
