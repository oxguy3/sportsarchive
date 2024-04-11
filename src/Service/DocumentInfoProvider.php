<?php

namespace App\Service;

use App\Entity\Document;

class DocumentInfoProvider
{
    /** @var array<string, array{'label': string, 'descriptor': string|null}> */
    public array $categoryData = [
        'branding' => [
            'label' => 'branding',
            'descriptor' => null,
        ],
        'directories' => [
            'label' => 'directories',
            'descriptor' => 'directory',
        ],
        'fact-sheets' => [
            'label' => 'fact sheets',
            'descriptor' => 'fact sheet',
        ],
        'game-notes' => [
            'label' => 'game notes',
            'descriptor' => null,
        ],
        'legal-documents' => [
            'label' => 'legal documents',
            'descriptor' => null,
        ],
        'media-guides' => [
            'label' => 'media guides',
            'descriptor' => 'media guide',
        ],
        'miscellany' => [
            'label' => 'miscellany',
            'descriptor' => null,
        ],
        'press-releases' => [
            'label' => 'press releases',
            'descriptor' => null,
        ],
        'programs' => [
            'label' => 'programs',
            'descriptor' => 'program',
        ],
        'record-books' => [
            'label' => 'record books',
            'descriptor' => 'record book',
        ],
        'reports' => [
            'label' => 'reports',
            'descriptor' => 'report',
        ],
        'rosters' => [
            'label' => 'rosters',
            'descriptor' => 'roster',
        ],
        'rule-books' => [
            'label' => 'rule books',
            'descriptor' => 'rule book',
        ],
        'schedules' => [
            'label' => 'schedules',
            'descriptor' => 'schedule',
        ],
        'season-reviews' => [
            'label' => 'season reviews',
            'descriptor' => 'season review',
        ],
        'yearbooks' => [
            'label' => 'yearbooks',
            'descriptor' => 'yearbook',
        ],
        'unsorted' => [
            'label' => 'unsorted',
            'descriptor' => null,
        ],
    ];

    /**
     * @return string[]
     */
    public function getCategories(): array
    {
        return array_keys($this->categoryData);
    }

    /**
     * @return array<string, string>
     */
    public function getCategoryLabels(): array
    {
        return array_map(function ($s) { return $s['label']; }, $this->categoryData);
    }

    /**
     * @return array<string, string>
     */
    public function getCategoryCapitalizedLabels(): array
    {
        return array_map(function ($s) { return ucfirst((string) $s['label']); }, $this->categoryData);
    }

    /**
     * @return array<string, string|null>
     */
    public function getCategoryDescriptors(): array
    {
        return array_map(function ($s) { return $s['descriptor']; }, $this->categoryData);
    }

    public function getCategoryLabel(string $category): string
    {
        return $this->categoryData[$category]['label'];
    }

    public function getCategoryDescriptor(string $category): ?string
    {
        return $this->categoryData[$category]['descriptor'];
    }

    public function isCategory(string $category): bool
    {
        return in_array($category, $this->getCategories());
    }

    /**
     * Generates a nice version of a document title
     */
    public function makeProperTitle(Document $document): string
    {
        $parenParts = explode(' (', (string) $document->getTitle(), 2);
        $commaParts = explode(', ', $parenParts[0], 2);
        $properTitle = $commaParts[0];

        $descriptor = $this->getCategoryDescriptor($document->getCategory());
        if ($descriptor) {
            $properTitle .= ' '.$descriptor;
        }
        if (count($commaParts) > 1) {
            $properTitle .= ', '.$commaParts[1];
        }
        if (count($parenParts) > 1) {
            $properTitle .= ' ('.$parenParts[1];
        }

        return $properTitle;
    }
}
