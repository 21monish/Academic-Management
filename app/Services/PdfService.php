<?php

namespace App\Services;

use Illuminate\Contracts\View\View;

class PdfService
{
    public function renderHtml(string $view, array $data = []): View
    {
        return view($view, $data);
    }
}
