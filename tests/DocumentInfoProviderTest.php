<?php

use App\Entity\Document;
use App\Service\DocumentInfoProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DocumentInfoProviderTest extends KernelTestCase
{
    private DocumentInfoProvider $documentInfo;

    #[Override]
    protected function setUp(): void
    {
        // retrieve Symfony service
        self::bootKernel();
        $container = static::getContainer();
        $this->documentInfo = $container->get(DocumentInfoProvider::class);
    }

    private function runTitleTest(string $title, string $category, string $expectedTitle): void
    {
        $document = new Document();
        $document->setTitle($title);
        $document->setCategory($category);

        $properTitle = $this->documentInfo->makeProperTitle($document);

        $this->assertSame($expectedTitle, $properTitle);
    }

    public function testTitles(): void
    {
        $this->runTitleTest('Banana', 'legal-documents', 'Banana');
        $this->runTitleTest('2017', 'record-books', '2017 record book');
        $this->runTitleTest('Some document (2020-03-15)', 'miscellany', 'Some document (2020-03-15)');
        $this->runTitleTest('1984, issue 3', 'programs', '1984 program, issue 3');
        $this->runTitleTest('1999 postseason, supplement (alt)', 'media-guides', '1999 postseason media guide, supplement (alt)');
    }
}
