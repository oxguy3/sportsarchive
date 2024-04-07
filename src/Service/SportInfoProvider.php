<?php

namespace App\Service;

class SportInfoProvider
{
    /** @var array<string, array{'name': string, 'icon': string}> */
    public array $data = [
        'baseball' => [
            'name' => 'baseball',
            'icon' => 'baseball-ball'
        ],
        'basketball' => [
            'name' => 'basketball',
            'icon' => 'basketball-ball'
        ],
        'bowling' => [
            'name' => 'bowling',
            'icon' => 'bowling-ball'
        ],
        'cricket' => [
            'name' => 'cricket',
            'icon' => 'cricket'
        ],
        'curling' => [
            'name' => 'curling',
            'icon' => 'curling-stone'
        ],
        'esports' => [
            'name' => 'esports',
            'icon' => 'gamepad'
        ],
        'football' => [
            'name' => 'football',
            'icon' => 'football-ball'
        ],
        'golf' => [
            'name' => 'golf',
            'icon' => 'golf-ball'
        ],
        'hockey' => [
            'name' => 'hockey',
            'icon' => 'hockey-puck'
        ],
        'lacrosse' => [
            'name' => 'lacrosse',
            'icon' => 'lacrosse'
        ],
        'mma' => [
            'name' => 'mixed martial arts',
            'icon' => 'mma'
        ],
        'motorsport' => [
            'name' => 'motorsport',
            'icon' => 'car-side'
        ],
        'multi-sport' => [
            'name' => 'multi-sport',
            'icon' => 'asterisk',
        ],
        'rugby' => [
            'name' => 'rugby',
            'icon' => 'rugby-ball'
        ],
        'soccer' => [
            'name' => 'soccer',
            'icon' => 'futbol'
        ],
        'table-tennis' => [
            'name' => 'table tennis',
            'icon' => 'table-tennis'
        ],
        'tennis' => [
            'name' => 'tennis',
            'icon' => 'tennis-racquet'
        ],
        'ultimate' => [
            'name' => 'ultimate',
            'icon' => 'flying-disc'
        ],
        'volleyball' => [
            'name' => 'volleyball',
            'icon' => 'volleyball-ball'
        ],
    ];

    /**
     * @return string[]
     */
    public function getSports(): array
    {
        return array_keys($this->data);
    }

    /**
     * @return array<string, string>
     */
    public function getNames(): array
    {
        return array_map(function($s) { return $s['name']; }, $this->data);
    }

    /**
     * @return array<string, string>
     */
    public function getCapitalizedNames(): array
    {
        return array_map(function($s) { return ucfirst((string) $s['name']); }, $this->data);
    }

    /**
     * @return array<string, string>
     */
    public function getIcons(): array
    {
        return array_map(function($s) { return $s['icon']; }, $this->data);
    }
    public function getName(string $sport): string
    {
        return $this->data[$sport]['name'];
    }
    public function getIcon(string $sport): string
    {
        return $this->data[$sport]['icon'];
    }
    public function isSport(string $str): bool
    {
        return in_array($str, $this->getSports());
    }
}
