<?php

namespace App\Service;

use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;

class PdfGenerator
{
    public function __construct(
        private HttpClientInterface $client,
        private Environment $twig,
        private string $gotenbergUrl,
    ) {
    }

    public function generateFromTwig(string $template, array $context = []): string
    {
        $html = $this->twig->render($template, $context);

        $formData = new FormDataPart([
            'files' => new DataPart($html, 'index.html', 'text/html'),
            'printBackground' => 'true',
            'marginTop' => '0',
            'marginBottom' => '0',
            'marginLeft' => '0',
            'marginRight' => '0',
        ]);

        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/html', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }
}