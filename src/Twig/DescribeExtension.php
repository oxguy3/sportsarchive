<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DescribeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('describe_document', [DescribeRuntime::class, 'describeDocument']),
            new TwigFilter('describe_team', [DescribeRuntime::class, 'describeTeam']),
        ];
    }
}
