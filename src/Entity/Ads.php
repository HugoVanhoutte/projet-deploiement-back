<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\Categories;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AdsRepository;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: AdsRepository::class)]
#[ApiResource(
    normalizationContext:['groups' => ['ads:read']],
    denormalizationContext:['groups' => ['ads:write']],
    operations: [
        new Get(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: '/api/ads/admin/detail/{id}',
            name:"app_ads_admin_detail"
        ), 
        new Get(
            security: "is_granted('ROLE_ADMIN')",
            description: "List all  ads.", 
            uriTemplate: '/api/ads/admin/listing',
            name:'app_ads_admin_listing'
        ),
        new Get(
           // description: "List all  ads  with isvirefied is ok.", 
            uriTemplate: '/api/ads/listing',
            name:'app_ads_listing'
        ),
        new Get(
            description: "List all  ads  with isvirefied is ok.", 
            uriTemplate: '/api/ads/admin/detail/{id}',
            name:'app_ads_admin_detail'
        ),
        new Post(), // conserver l'opération de création
        new Put(
            description: "Delete an ads.", 
            uriTemplate: '/api/ads/verified/{adsId}',
            name:'app_ads_admin_changeVerfied',
        ), // conserver l'opération de mise à jour
        new delete(security: "is_granted('ROLE_ADMIN')"),
        new Delete( 
            description: "Delete an ads.", 
            uriTemplate: '/api/ads/delete/{adsId}/{userId}',
            name:'app_ads_delete',
        ), 
        new Post(   
            description: "Create an ads.", 
            uriTemplate: '/api/ads/create',
            name:'app_ads_create',
        )   
    ]
)]
class Ads
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['ads:read','ads:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['ads:read','ads:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['ads:read','ads:write'])]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column(length: 5)]
    #[Groups(['ads:read','ads:write'])]
    private ?string $zipCode = null;

    #[ORM\Column]
    #[Groups(['ads:read','ads:write'])]
    private ?int $width = null;

    #[ORM\Column]
    #[Groups(['ads:read','ads:write'])]
    private ?int $length = null;

    #[ORM\Column]
    #[Groups(['ads:read','ads:write'])]
    private ?int $height = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['ads:read','ads:write'])]
    private ?array $images = null;

    #[ORM\Column]
    #[Groups(['ads:read','ads:write'])]
    private ?bool $isVerified = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'ads', fetch: 'EXTRA_LAZY', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['ads:read', 'ads:write'])]
    private ?User $user = null;

    /**
     * @var Collection<int, Categories>
     */
    #[ORM\ManyToMany(targetEntity: Categories::class)]
    #[Groups(['ads:read','ads:write'])]
    private Collection $isIn;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[Groups(['ads:read','ads:write'])]
    private Collection $reporting;

    public function __construct()
    {
        $this->isIn = new ArrayCollection();
        $this->reporting = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(int $length): static
    {
        $this->length = $length;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(?array $images): static
    {
        $this->images = $images;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Categories>
     */
    public function getIsIn(): Collection
    {
        return $this->isIn;
    }

    public function addIsIn(Categories $isIn): static
    {
        if (!$this->isIn->contains($isIn)) {
            $this->isIn->add($isIn);
        }

        return $this;
    }

    public function removeIsIn(Categories $isIn): static
    {
        $this->isIn->removeElement($isIn);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getReporting(): Collection
    {
        return $this->reporting;
    }

    public function addReporting(User $reporting): static
    {
        if (!$this->reporting->contains($reporting)) {
            $this->reporting->add($reporting);
        }

        return $this;
    }

    public function removeReporting(User $reporting): static
    {
        $this->reporting->removeElement($reporting);

        return $this;
    }
}
