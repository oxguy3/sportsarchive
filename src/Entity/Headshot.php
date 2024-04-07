<?php

namespace App\Entity;

use App\Repository\HeadshotRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HeadshotRepository::class)]
class Headshot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $personName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $jerseyNumber = null;

    #[ORM\ManyToOne(targetEntity: Roster::class, inversedBy: 'headshots')]
    private Roster $roster;

    #[ORM\Column(type: 'string', length: 255)]
    private string $filename;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => 'player'])]
    #[Assert\Choice(['player', 'staff'])]
    private string $role = 'player';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $title = null;

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

    public function getFileUrl(): ?string
    {
        $url = $_ENV['S3_ENDPOINT'].'/';
        $url .= $_ENV['S3_HEADSHOTS_BUCKET'].'/'.$_ENV['S3_PREFIX'];
        $url .= $this->getFilename();

        return $url;
    }

    public function getThumbnailUrl(): ?string
    {
        $url = 'https://imgproxy.sportsarchive.net/sig/fit/300/0/ce/0/plain/s3://';
        $url .= $_ENV['S3_HEADSHOTS_BUCKET'].'/'.$_ENV['S3_PREFIX'];
        $url .= $this->getFilename();

        return $url;
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
