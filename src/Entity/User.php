<?php

namespace App\Entity;

use App\Entity\Ads;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use ApiPlatform\Symfony\Bundle\SwaggerUi\SwaggerUiContext;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
#[ORM\Entity(repositoryClass: UserRepository::class)]

#[ApiResource(
    normalizationContext:['groups' => ['user:read']],
    denormalizationContext:['groups' => ['user:write']],
    operations: [
        // new Get(), 
        // new Post(), 
        new Put(
            security: "is_granted('ROLE_USER')",
            description: "Updete his account", 
            uriTemplate: '/api/user/update/{id}',
            name:'app_user_update'
        ), 
        new Delete(
            security: "is_granted('ROLE_USER')",
            description: "Delete his account", 
            uriTemplate: '/api/user/delete/{id}',
            name:'app_user_delete'
        ),
         new Delete(
             security: "is_granted('ROLE_ADMIN')",
            description: "Delete an account", 
            uriTemplate: '/api/user/admin/delete/{id}',
            name:'app_user_admin_delete'
         ), 
        new Post(   
            description: "Enregistre un nouveau client.", 
            uriTemplate: '/api/user/register',
            name:'app_user_register',
        )   
    ]
)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ["email"], message: "Cet email est déjà utilisé.")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read','user:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read','user:write'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user:read','user:write'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 25)]
    #[Groups(['user:read','user:write'])]
    private ?string $userName = null;

    #[ORM\Column(length: 10)]
    #[Groups(['user:read','user:write'])]
    private ?string $phone = null;

    #[ORM\Column(length: 100)]
    #[Groups(['user:read','user:write'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Groups(['user:read','user:write'])]
    private ?string $lastName = null;



    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Ads::class, cascade: ['remove'], orphanRemoval: true)]
private Collection $ads;
    /**
     * @var Collection<int, Ads>
     */
    #[ORM\ManyToMany(targetEntity: Ads::class)]
    
    private Collection $isFavorite;

    public function __construct()
    {
        $this->isFavorite = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): static
    {
        $this->userName = $userName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return Collection<int, Ads>
     */
    public function getIsFavorite(): Collection
    {
        return $this->isFavorite;
    }

    public function addIsFavorite(Ads $isFavorite): static
    {
        if (!$this->isFavorite->contains($isFavorite)) {
            $this->isFavorite->add($isFavorite);
        }

        return $this;
    }

    public function removeIsFavorite(Ads $isFavorite): static
    {
        $this->isFavorite->removeElement($isFavorite);

        return $this;
    }
}
