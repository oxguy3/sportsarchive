<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Team defines a team, league, or other organization.
 */
#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[UniqueEntity('slug')]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Regex('/^[a-z0-9-]+$/')]
    private string $slug;

    /** @var Collection<string|int, Roster> */
    #[ORM\OneToMany(targetEntity: Roster::class, mappedBy: 'team')]
    private Collection $rosters;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $website = null;

    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    #[Assert\Country]
    private ?string $country = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $startYear = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $endYear = null;

    #[ORM\Column(type: 'string', length: 16, nullable: true)]
    #[Assert\Choice(['men', 'women'])]
    private ?string $gender = null;

    #[ORM\Column(type: 'string', length: 16, nullable: true)]
    #[AppAssert\IsSport()]
    private ?string $sport = null;

    /** @var Collection<string|int, Document> */
    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'team')]
    private Collection $documents;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $logoFileType = null;

    #[ORM\Column(type: 'string', length: 16, nullable: true)]
    #[Assert\Choice(['teams', 'orgs'])]
    private ?string $type = null;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'subTeams')]
    private ?Team $parentTeam = null;

    /** @var Collection<string|int, Team> */
    #[ORM\OneToMany(targetEntity: Team::class, mappedBy: 'parentTeam')]
    private Collection $subTeams;

    /** @var Collection<string|int, TeamLeague> */
    #[ORM\OneToMany(targetEntity: TeamLeague::class, mappedBy: 'team')]
    private Collection $teamLeagues;

    /** @var Collection<string|int, TeamLeague> */
    #[ORM\OneToMany(targetEntity: TeamLeague::class, mappedBy: 'league')]
    private Collection $memberTeamLeagues;

    public function __construct()
    {
        $this->rosters = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->subTeams = new ArrayCollection();
        $this->teamLeagues = new ArrayCollection();
        $this->memberTeamLeagues = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
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
     * @return Collection<string|int, Roster>
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
        $pretty = preg_replace('/^https?:\\/\\/(.*)$/', '$1', (string) $this->website);
        $pretty = preg_replace("/^www\.(.*)$/", '$1', (string) $pretty);
        $pretty = preg_replace('/^(.*)\\/$/', '$1', (string) $pretty);

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
     * @return Collection<string|int, Document>
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
            $url = $_ENV['S3_ENDPOINT'].'/';
            $url .= $_ENV['S3_LOGOS_BUCKET'].'/'.$_ENV['S3_PREFIX'];
            $url .= $this->slug.'.'.$this->logoFileType;

            return $url;
        } else {
            return '/images/placeholder-logo.svg';
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

    /**
     * @return Collection<string|int, Team>
     */
    public function getSubTeams(): Collection
    {
        return $this->subTeams;
    }

    public function addSubTeam(Team $subTeam): self
    {
        if (!$this->subTeams->contains($subTeam)) {
            $this->subTeams[] = $subTeam;
            $subTeam->setParentTeam($this);
        }

        return $this;
    }

    public function removeSubTeam(Team $subTeam): self
    {
        if ($this->subTeams->removeElement($subTeam)) {
            // set the owning side to null (unless already changed)
            if ($subTeam->getParentTeam() === $this) {
                $subTeam->setParentTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<string|int, TeamLeague>
     */
    public function getTeamLeagues(): Collection
    {
        return $this->teamLeagues;
    }

    public function addTeamLeague(TeamLeague $teamLeague): self
    {
        if (!$this->teamLeagues->contains($teamLeague)) {
            $this->teamLeagues[] = $teamLeague;
            $teamLeague->setTeam($this);
        }

        return $this;
    }

    public function removeTeamLeague(TeamLeague $teamLeague): self
    {
        if ($this->teamLeagues->removeElement($teamLeague)) {
            // set the owning side to null (unless already changed)
            if ($teamLeague->getTeam() === $this) {
                $teamLeague->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<string|int, TeamLeague>
     */
    public function getMemberTeamLeagues(): Collection
    {
        return $this->memberTeamLeagues;
    }

    public function addMemberTeamLeague(TeamLeague $memberTeamLeague): self
    {
        if (!$this->memberTeamLeagues->contains($memberTeamLeague)) {
            $this->memberTeamLeagues[] = $memberTeamLeague;
            $memberTeamLeague->setLeague($this);
        }

        return $this;
    }

    public function removeMemberTeamLeague(TeamLeague $memberTeamLeague): self
    {
        if ($this->memberTeamLeagues->removeElement($memberTeamLeague)) {
            // set the owning side to null (unless already changed)
            if ($memberTeamLeague->getLeague() === $this) {
                $memberTeamLeague->setLeague(null);
            }
        }

        return $this;
    }
}
