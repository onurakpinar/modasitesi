<?php

namespace App\Http\Controllers;

use App\Support\Ads\AdSettings;
use Illuminate\Http\Response;

class AdsTxtController extends Controller
{
    public function index(): Response
    {
        $publisherId = AdSettings::publisherId();

        if ($publisherId === null) {
            $content = "# ads.txt\n# Publisher ID henüz yapılandırılmadı. Admin panelinden geçerli pub- kimliği girin.\n";

            return $this->response($content);
        }

        $content = "google.com, {$publisherId}, DIRECT, f08c47fec0942fa0\n";

        return $this->response($content);
    }

    private function response(string $content): Response
    {
        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
