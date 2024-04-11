<?php

namespace App\Twig;

use App\Entity\Document;
use App\Entity\Team;
use App\Service\DocumentInfoProvider;
use App\Service\SportInfoProvider;
use Twig\Extension\RuntimeExtensionInterface;

class DescribeRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly SportInfoProvider $sportInfo,
        private readonly DocumentInfoProvider $documentInfo,
    ) {}

    /**
     * Generates a proper title for a Document
     */
    public function describeDocument(Document $document): string
    {
        return $this->documentInfo->makeProperTitle($document);
    }

    /**
     * Generates a short description of a Team
     */
    public function describeTeam(Team $team): ?string
    {
        $description = '';
        if ($team->getGender() != null) {
            $description .= $team->getGender()."'s ";
        }
        if ($team->getSport() != null) {
            $description .= $this->sportInfo->getName($team->getSport()).' ';
        }
        if ($team->getType() == 'teams') {
            $description .= 'team ';
        } elseif ($team->getType() == 'orgs') {
            $description .= 'organization ';
        }
        if ($team->getCountry() != null) {
            $description .= 'from '.$team->getCountryName().' ';
        }
        $description = ucfirst(rtrim($description));

        return $description;
    }
}
