<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 */
class Document
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
    private $fileId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="documents")
     */
    private $team;

    /**
     * @ORM\Column(type="string", length=255, options={"default" : "unsorted"})
     * @Assert\Choice({"unsorted", "branding", "directories", "game-notes", "legal-documents", "media-guides", "miscellany", "programs", "record-books", "rosters", "rule-books", "schedules", "season-reviews"})
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Language
     */
    private $language;

    /**
     * @ORM\Column(type="string", length=10000, nullable=true)
     */
    private $notes;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $isBookReader = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileId(): ?string
    {
        return $this->fileId;
    }

    public function setFileId(string $fileId): self
    {
        $this->fileId = $fileId;

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
        $url .= $_ENV['S3_DOCUMENTS_BUCKET'].'/'.$_ENV['S3_PREFIX'];
        $url .= $this->getFilePath();
        return $url;
    }

    public function getFilePath(): ?string
    {
        return $this->fileId.'/'.$this->filename;
    }

    public function getFileExtension(): ?string
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;

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

    public function getIsBookReader(): ?bool
    {
        return $this->isBookReader;
    }

    public function setIsBookReader(?bool $isBookReader): self
    {
        $this->isBookReader = $isBookReader;

        return $this;
    }
}
