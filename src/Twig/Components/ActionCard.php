<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ActionCard
{
    public string $title;
    public string $subtitle;
    public string $path = '#';
    public string $icon = '';
    public string $theme = 'default';
}
