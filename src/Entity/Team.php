<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Intl\Countries;
use App\Service\SportInfoProvider;
use App\Validator as AppAssert;

/**
 * @ORM\Entity(repositoryClass=TeamRepository::class)
 * @UniqueEntity("slug")
 */
class Team
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity=Roster::class, mappedBy="team")
     */
    private $rosters;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url
     */
    private $website;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     * @Assert\Country
     */
    private $country;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $startYear;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $endYear;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Assert\Choice({"men", "women"})
     */
    private $gender;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @AppAssert\IsSport()
     */
    private $sport;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="team")
     */
    private $documents;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logoFileType;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Assert\Choice({"teams", "orgs"})
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class)
     */
    private $parentTeam;

    public function __construct()
    {
        $this->rosters = new ArrayCollection();
        $this->documents = new ArrayCollection();
    }

    public function getDescription(): ?string
    {
        $description = '';
        if ($this->gender != null) {
            $description .= $this->gender."'s ";
        }
        if ($this->sport != null) {
            // TODO: you're not supposed to use services inside entities
            $sportInfo = new SportInfoProvider();
            $description .= $sportInfo->getName($this->sport).' ';
        }
        if ($this->type == 'teams') {
            $description .= 'team ';
        } else if ($this->type == 'orgs') {
            $description .= 'organization ';
        }
        if ($this->country != null) {
            $description .= "from ".$this->getCountryName()." ";
        }
        $description = ucfirst(rtrim($description));
        return $description;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|Roster[]
     */
    public function getRosters(): Collection
    {
        return $this->rosters;
    }

    public function addRoster(Roster $roster): self
    {
        if (!$this->rosters->contains($roster)) {
            $this->rosters[] = $roster;
            $roster->setTeam($this);
        }

        return $this;
    }

    public function removeRoster(Roster $roster): self
    {
        if ($this->rosters->removeElement($roster)) {
            // set the owning side to null (unless already changed)
            if ($roster->getTeam() === $this) {
                $roster->setTeam(null);
            }
        }

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function getWebsitePretty(): ?string
    {
        if ($this->website == null) {
            return null;
        }
        $pretty = preg_replace("/^https?:\\/\\/(.*)$/", "$1", $this->website);
        $pretty = preg_replace("/^www\.(.*)$/", "$1", $pretty);
        $pretty = preg_replace("/^(.*)\\/$/", "$1", $pretty);
        return $pretty;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCountryName(): ?string
    {
        return Countries::getName($this->country);
    }

    public function getStartYear(): ?int
    {
        return $this->startYear;
    }

    public function setStartYear(?int $startYear): self
    {
        $this->startYear = $startYear;

        return $this;
    }

    public function getEndYear(): ?int
    {
        return $this->endYear;
    }

    public function setEndYear(?int $endYear): self
    {
        $this->endYear = $endYear;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getSport(): ?string
    {
        return $this->sport;
    }

    public function setSport(?string $sport): self
    {
        $this->sport = $sport;

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setTeam($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getTeam() === $this) {
                $document->setTeam(null);
            }
        }

        return $this;
    }

    public function getLogoFileType(): ?string
    {
        return $this->logoFileType;
    }

    public function setLogoFileType(?string $logoFileType): self
    {
        $this->logoFileType = $logoFileType;

        return $this;
    }

    public function getLogoUrl(): ?string
    {
        if ($this->logoFileType != null) {
            $url = $_ENV['S3_ENDPOINT'] . '/';
            $url .= $_ENV['S3_LOGOS_BUCKET'] . '/' . $_ENV['S3_PREFIX'];
            $url .= $this->slug . '.' . $this->logoFileType;
            return $url;
        } else {
            return "/images/placeholder-logo.svg";
        }
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getParentTeam(): ?self
    {
        return $this->parentTeam;
    }

    public function setParentTeam(?self $parentTeam): self
    {
        $this->parentTeam = $parentTeam;

        return $this;
    }
}
