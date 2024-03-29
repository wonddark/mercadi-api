<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Controller\CreateMediaObjectAction;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity]
#[ApiResource(
    types: ['https://schema.org/MediaObject'],
    normalizationContext: ['groups' => ['media_object:read']]
)]
#[Get]
#[Post(
    controller: CreateMediaObjectAction::class,
    openapiContext: [
        'requestBody' => [
            'content' => [
                'multipart/form-data' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'file' => [
                                'type' => 'string',
                                'format' => 'binary'
                            ],
                            'item_id' => [
                                'type' => 'string',
                                'format' => 'string'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    validationContext: ['groups' => ['Default', 'media_object_create']],
    deserialize: false
)]
#[Delete]
class MediaObject
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups([
        'media_object:read'
    ])]
    private ?Uuid $id = null;

    #[ApiProperty(types: ['https://schema.org/contentUrl'])]
    #[Groups([
        'media_object:read',
        "item:post:read",
        "item:patch:read",
        "item:general:read"
    ])]
    public ?string $contentUrl = null;

    #[Vich\UploadableField(mapping: "media_object", fileNameProperty: "filePath")]
    #[Assert\NotNull(groups: ['media_object_create'])]
    public ?File $file = null;

    #[ORM\Column(nullable: true)]
    public ?string $filePath = null;

    #[ORM\ManyToOne(inversedBy: 'medias')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['media_object:read'])]
    private ?Item $item = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    /**
     * @return string|null
     * @noinspection PhpUnused
     */
    public function getContentUrl(): ?string
    {
        return $this->contentUrl;
    }

    /**
     * @param string|null $contentUrl
     * @noinspection PhpUnused
     */
    public function setContentUrl(?string $contentUrl): void
    {
        $this->contentUrl = $contentUrl;
    }
}
