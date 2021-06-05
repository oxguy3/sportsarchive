<?php

namespace App\Entity;

use App\Repository\HeadshotRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=HeadshotRepository::class)
 */
class Headshot
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $personName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $jerseyNumber;

    /**
     * @ORM\ManyToOne(targetEntity=Roster::class, inversedBy="headshots")
     */
    private $roster;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=255, options={"default" : "player"})
     * @Assert\Choice({"player", "staff"})
     */
    private $role = "player";

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPersonName(): ?string
    {
        return $this->personName;
    }

    public function setPersonName(string $personName): self
    {
        $this->personName = $personName;

        return $this;
    }

    public function getJerseyNumber(): ?string
    {
        return $this->jerseyNumber;
    }

    public function setJerseyNumber(?string $jerseyNumber): self
    {
        $this->jerseyNumber = $jerseyNumber;

        return $this;
    }

    public function getRoster(): ?Roster
    {
        return $this->roster;
    }

    public function setRoster(?Roster $roster): self
    {
        $this->roster = $roster;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
