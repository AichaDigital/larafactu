<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Avatar service for generating and managing user avatars.
 *
 * Priority:
 * 1. Custom uploaded avatar (stored locally)
 * 2. Gravatar (if exists)
 * 3. Generated geometric icon (based on email hash)
 */
class AvatarService
{
    protected ImageManager $manager;

    protected int $defaultSize = 128;

    /**
     * Color palette for generated avatars (DaisyUI theme aligned).
     *
     * @var array<string>
     */
    protected array $palette = [
        '#65c3c8', // cupcake primary
        '#ef9fbc', // cupcake secondary
        '#eeaf3a', // cupcake accent
        '#00d27f', // abyss primary
        '#b794f4', // abyss secondary
        '#3b82f6', // info blue
        '#22c55e', // success green
        '#f59e0b', // warning amber
        '#ef4444', // error red
        '#6366f1', // indigo
        '#8b5cf6', // violet
        '#ec4899', // pink
        '#14b8a6', // teal
        '#f97316', // orange
        '#06b6d4', // cyan
    ];

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
    }

    /**
     * Get avatar URL for a user.
     *
     * Priority: custom > gravatar > generated
     */
    public function getAvatarUrl(User $user, int $size = 128): string
    {
        // 1. Custom uploaded avatar
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            return Storage::disk('public')->url($user->avatar_path);
        }

        // 2. Gravatar (check if exists)
        $gravatarUrl = $this->getGravatarUrl($user->email, $size);
        if ($this->gravatarExists($user->email)) {
            return $gravatarUrl;
        }

        // 3. Generated avatar (return as data URI or route)
        return route('avatar.generate', ['email' => $user->email, 'size' => $size]);
    }

    /**
     * Check if user has a Gravatar.
     */
    public function gravatarExists(string $email): bool
    {
        $hash = md5(strtolower(trim($email)));
        $url = "https://www.gravatar.com/avatar/{$hash}?d=404";

        try {
            $response = Http::timeout(3)->head($url);

            return $response->status() === 200;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Get Gravatar URL for an email.
     */
    public function getGravatarUrl(string $email, int $size = 128): string
    {
        $hash = md5(strtolower(trim($email)));

        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=404";
    }

    /**
     * Generate a geometric avatar SVG based on email hash.
     *
     * Creates a unique pattern using shapes (no letters).
     */
    public function generateSvg(string $email, int $size = 128): string
    {
        $hash = md5(strtolower(trim($email)));

        // Select colors based on hash
        $bgColorIndex = hexdec(substr($hash, 0, 2)) % count($this->palette);
        $fgColorIndex = (hexdec(substr($hash, 2, 2)) + 5) % count($this->palette);

        $bgColor = $this->palette[$bgColorIndex];
        $fgColor = $this->palette[$fgColorIndex];

        // Ensure contrast - if colors are too similar, shift foreground
        if ($bgColorIndex === $fgColorIndex) {
            $fgColor = '#ffffff';
        }

        // Generate pattern based on hash
        $pattern = $this->generatePattern($hash, $size, $fgColor);

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$size} {$size}" width="{$size}" height="{$size}">
            <rect width="{$size}" height="{$size}" fill="{$bgColor}" rx="{$size}"/>
            {$pattern}
        </svg>
        SVG;
    }

    /**
     * Generate geometric pattern based on hash.
     */
    protected function generatePattern(string $hash, int $size, string $color): string
    {
        $patterns = [];
        $center = $size / 2;
        $segments = 8;

        // Determine pattern type from hash
        $patternType = hexdec(substr($hash, 4, 2)) % 4;

        // Generate symmetrical pattern (mirrored for visual appeal)
        for ($i = 0; $i < $segments; $i++) {
            $hashSegment = substr($hash, ($i * 2) % 28, 4);
            $value = hexdec($hashSegment);

            // Only draw if bit is set (50% density)
            if ($value % 2 === 0) {
                continue;
            }

            $angle = ($i / $segments) * 2 * M_PI;
            $radius = ($size * 0.25) + (($value % 32) / 32 * $size * 0.15);

            switch ($patternType) {
                case 0: // Circles
                    $x = $center + cos($angle) * $radius * 0.5;
                    $y = $center + sin($angle) * $radius * 0.5;
                    $r = 8 + ($value % 12);
                    $patterns[] = "<circle cx=\"{$x}\" cy=\"{$y}\" r=\"{$r}\" fill=\"{$color}\" opacity=\"0.9\"/>";
                    break;

                case 1: // Squares (rotated)
                    $x = $center + cos($angle) * $radius * 0.5;
                    $y = $center + sin($angle) * $radius * 0.5;
                    $s = 10 + ($value % 14);
                    $rotation = $angle * (180 / M_PI);
                    $patterns[] = '<rect x="'.($x - $s / 2).'" y="'.($y - $s / 2)."\" width=\"{$s}\" height=\"{$s}\" fill=\"{$color}\" opacity=\"0.9\" transform=\"rotate({$rotation} {$x} {$y})\"/>";
                    break;

                case 2: // Triangles
                    $x = $center + cos($angle) * $radius * 0.5;
                    $y = $center + sin($angle) * $radius * 0.5;
                    $s = 12 + ($value % 10);
                    $points = $this->trianglePoints($x, $y, $s, $angle);
                    $patterns[] = "<polygon points=\"{$points}\" fill=\"{$color}\" opacity=\"0.9\"/>";
                    break;

                case 3: // Lines/rays
                    $x1 = $center + cos($angle) * $size * 0.2;
                    $y1 = $center + sin($angle) * $size * 0.2;
                    $x2 = $center + cos($angle) * $size * 0.4;
                    $y2 = $center + sin($angle) * $size * 0.4;
                    $strokeWidth = 4 + ($value % 6);
                    $patterns[] = "<line x1=\"{$x1}\" y1=\"{$y1}\" x2=\"{$x2}\" y2=\"{$y2}\" stroke=\"{$color}\" stroke-width=\"{$strokeWidth}\" stroke-linecap=\"round\" opacity=\"0.9\"/>";
                    break;
            }
        }

        // Add central shape
        $centralType = hexdec(substr($hash, 6, 2)) % 3;
        $centralSize = $size * 0.2;

        switch ($centralType) {
            case 0:
                $patterns[] = "<circle cx=\"{$center}\" cy=\"{$center}\" r=\"{$centralSize}\" fill=\"{$color}\" opacity=\"0.7\"/>";
                break;
            case 1:
                $half = $centralSize;
                $patterns[] = '<rect x="'.($center - $half).'" y="'.($center - $half).'" width="'.($half * 2).'" height="'.($half * 2)."\" fill=\"{$color}\" opacity=\"0.7\" rx=\"4\"/>";
                break;
            case 2:
                $points = $this->trianglePoints($center, $center, $centralSize * 1.5, 0);
                $patterns[] = "<polygon points=\"{$points}\" fill=\"{$color}\" opacity=\"0.7\"/>";
                break;
        }

        return implode("\n", $patterns);
    }

    /**
     * Generate triangle points.
     */
    protected function trianglePoints(float $cx, float $cy, float $size, float $rotation): string
    {
        $points = [];
        for ($i = 0; $i < 3; $i++) {
            $angle = $rotation + ($i * 2 * M_PI / 3) - (M_PI / 2);
            $x = $cx + cos($angle) * $size;
            $y = $cy + sin($angle) * $size;
            $points[] = round($x, 2).','.round($y, 2);
        }

        return implode(' ', $points);
    }

    /**
     * Upload and store avatar for user.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     */
    public function uploadAvatar(User $user, $file): string
    {
        // Delete old avatar if exists
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Resize and optimize
        $image = $this->manager->read($file->getPathname());
        $image->cover($this->defaultSize * 2, $this->defaultSize * 2); // 2x for retina

        // Generate filename
        $filename = 'avatars/'.$user->id.'_'.time().'.webp';

        // Store as WebP for optimal size
        Storage::disk('public')->put(
            $filename,
            $image->toWebp(quality: 85)->toString()
        );

        // Update user
        $user->update(['avatar_path' => $filename]);

        return $filename;
    }

    /**
     * Delete user's custom avatar.
     */
    public function deleteAvatar(User $user): void
    {
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }
    }

    /**
     * Get avatar as binary PNG data (for API responses).
     */
    public function getAvatarImage(string $email, int $size = 128): string
    {
        $svg = $this->generateSvg($email, $size);

        // For binary conversion, we'd need additional processing
        // For now, return SVG wrapped in image data
        return $svg;
    }
}
