<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AvatarService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AvatarController extends Controller
{
    public function __construct(
        protected AvatarService $avatarService
    ) {}

    /**
     * Generate and serve avatar SVG.
     */
    public function generate(Request $request): Response
    {
        $email = $request->query('email', 'default@example.com');
        $size = (int) $request->query('size', 128);

        // Clamp size
        $size = max(32, min(512, $size));

        $svg = $this->avatarService->generateSvg($email, $size);

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=31536000'); // Cache for 1 year
    }
}
