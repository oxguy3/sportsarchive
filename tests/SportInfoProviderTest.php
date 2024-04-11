<?php

use App\Service\SportInfoProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SportInfoProviderTest extends KernelTestCase
{
    private SportInfoProvider $sportInfo;

    protected function setUp(): void
    {
        // retrieve Symfony service
        self::bootKernel();
        $container = static::getContainer();
        $this->sportInfo = $container->get(SportInfoProvider::class);
    }

    public function testReturnTypes(): void
    {
        $this->assertContainsOnly('string', $this->sportInfo->getSports());
        $this->assertContainsOnly('string', $this->sportInfo->getNames());
        $this->assertContainsOnly('string', $this->sportInfo->getCapitalizedNames());
        $this->assertContainsOnly('string', $this->sportInfo->getIcons());
    }

    public function testNames(): void
    {
        $this->assertSame($this->sportInfo->getName('soccer'), 'soccer');
        $this->assertSame($this->sportInfo->getName('mma'), 'mixed martial arts');
        $this->assertSame($this->sportInfo->getName('banana'), null);
    }

    public function testIcons(): void
    {
        $this->assertSame($this->sportInfo->getIcon('soccer'), 'futbol');
        $this->assertSame($this->sportInfo->getIcon('lacrosse'), 'lacrosse');
        $this->assertSame($this->sportInfo->getIcon('apple'), null);
    }

    public function testIsSport(): void
    {
        $this->assertTrue($this->sportInfo->isSport('baseball'));
        $this->assertTrue($this->sportInfo->isSport('multi-sport'));
        $this->assertFalse($this->sportInfo->isSport('banana'));
        $this->assertFalse($this->sportInfo->isSport('multi sport'));
    }
}
