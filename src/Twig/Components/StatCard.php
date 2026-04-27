<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class StatCard
{
    public string $label = "test";
    public string|int $value = 10;
    public string $icon;
    public string $color;
}
