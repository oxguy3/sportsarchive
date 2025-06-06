<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableEntity;
use App\Repository\DocumentRepository;
use App\Validator as AppAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document implements \Stringable
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $fileId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $filename;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'documents')]
    private Team $team;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => 'unsorted'])]
    #[AppAssert\IsDocumentCategory()]
    private string $category = 'unsorted';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Language]
    private ?string $language = null;

    #[ORM\Column(type: 'string', length: 10000, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'boolean', options: ['default' => '0'])]
    private bool $isBookReader = false;

    #[\Override]
    public function __toString(): string
    {
        return $this->category.': '.$this->title.' ['.$this->language.']';
    }

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
        return pathinfo((string) $this->filename, PATHINFO_EXTENSION);
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
