<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\OrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
#[ApiResource(
    description: 'The Organization entity represents a business or group that
     uses the bookkeeping application. An organization typically corresponds 
     to a company, non-profit, or other type of entity that manages financial
      records within the app. Each organization can be owned and managed by
       one or more users.',
    operations: [
        new Get(security: 'object.isUserInOrganization(user)'),
        new Post(security: 'is_granted("ROLE_USER")'),
        new Put(),
        new Delete(security: 'object.isUserInOrganization(user)'),
        new GetCollection(),
    ]
)]
class Organization
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'organizations')]
    private Collection $users;

    #[ORM\OneToMany(targetEntity: Journal::class, mappedBy: 'organization')]
    private Collection $journals;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->journals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function isUserInOrganization(User $user): bool
    {
        if (!$this->users->isInitialized()) {
            $this->users->initialize();
        }
        dump($this->users);
        dump($this->users->toArray());
        dump($user);
        return $this->users->contains($user);
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function getJournals(): Collection
    {
        return $this->journals;
    }

    public function addJournal(Journal $journal): static
    {
        if (!$this->journals->contains($journal)) {
            $this->journals->add($journal);
        }

        return $this;
    }

    public function removeJournal(Journal $journal): static
    {
        $this->journals->removeElement($journal);

        return $this;
    }

    // public function getJournals(): ?Journal
    // {
    //     return $this->journal;
    // }

    // public function setJournals(?Journal $journal): static
    // {
    //     $this->journal = $journal;

    //     return $this;
    // }
}
