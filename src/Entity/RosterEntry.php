<?php

namespace App\Entity;

use App\Repository\RosterEntryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RosterEntryRepository::class)
 */
class RosterEntry
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
     * @ORM\Column(type="string", length=255)
     */
    private $jerseyNumber;

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

    public function setJerseyNumber(string $jerseyNumber): self
    {
        $this->jerseyNumber = $jerseyNumber;

        return $this;
    }
}
