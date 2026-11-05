<?php

declare(strict_types=1);

namespace App\Http;

final class View
{
    public function __construct(private readonly string $templateDir)
    {
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require $this->templateDir . '/' . $template . '.php';
        $content = (string) ob_get_clean();

        ob_start();
        require $this->templateDir . '/layout.php';

        return (string) ob_get_clean();
    }
}
