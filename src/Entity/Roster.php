<?php

namespace App\Entity;

use App\Repository\RosterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RosterRepository::class)
 */
class Roster
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $year;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="rosters")
     */
    private $team;

    /**
     * @ORM\OneToMany(targetEntity=Headshot::class, mappedBy="roster")
     */
    private $headshots;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
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
     * @return Collection|Headshot[]
     */
    public function getHeadshots(): Collection
    {
        return $this->entries;
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
}
