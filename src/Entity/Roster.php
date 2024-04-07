<?php

namespace App\Entity;

use App\Repository\RosterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RosterRepository::class)]
#[UniqueEntity(fields: ['team', 'year'], errorPath: 'year', message: 'This team already has a roster for that year.')]
class Roster
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Regex(pattern: '/^[\d-]+$/', message: 'Years can only consist of numbers and dashes.')]
    private string $year;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'rosters')]
    private Team $team;

    /** @var Collection<string|int, Headshot> */
    #[ORM\OneToMany(targetEntity: Headshot::class, mappedBy: 'roster')]
    private Collection $headshots;

    #[ORM\Column(type: 'string', length: 255)]
    private string $teamName;

    #[ORM\Column(type: 'string', length: 1000, nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->headshots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(string $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return Collection<string|int, Headshot>
     */
    public function getHeadshots(): Collection
    {
        return $this->headshots;
    }

    public function addHeadshot(Headshot $headshot): self
    {
        if (!$this->headshots->contains($headshot)) {
            $this->headshots[] = $headshot;
            $headshot->setRoster($this);
        }

        return $this;
    }

    public function removeEntry(Headshot $headshot): self
    {
        if ($this->headshots->removeElement($headshot)) {
            // set the owning side to null (unless already changed)
            if ($headshot->getRoster() === $this) {
                $headshot->setRoster(null);
            }
        }

        return $this;
    }

    public function getTeamName(): ?string
    {
        return $this->teamName;
    }

    public function setTeamName(?string $teamName): self
    {
        $this->teamName = $teamName;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }
}
