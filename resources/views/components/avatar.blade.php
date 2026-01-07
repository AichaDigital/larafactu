@props([
    'user' => null,
    'email' => null,
    'size' => 'md',
    'class' => '',
])

@php
    // Size mapping
    $sizes = [
        'xs' => 'w-6 h-6',
        'sm' => 'w-8 h-8',
        'md' => 'w-10 h-10',
        'lg' => 'w-12 h-12',
        'xl' => 'w-16 h-16',
        '2xl' => 'w-20 h-20',
        '3xl' => 'w-24 h-24',
    ];

    $pixelSizes = [
        'xs' => 24,
        'sm' => 32,
        'md' => 40,
        'lg' => 48,
        'xl' => 64,
        '2xl' => 80,
        '3xl' => 96,
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $pixelSize = $pixelSizes[$size] ?? $pixelSizes['md'];

    // Determine avatar source
    $avatarUrl = null;
    $emailToUse = $email ?? ($user?->email ?? 'default@example.com');

    if ($user) {
        $avatarService = app(\App\Services\AvatarService::class);
        $avatarUrl = $avatarService->getAvatarUrl($user, $pixelSize * 2); // 2x for retina
    } else {
        // Fallback to generated avatar
        $avatarUrl = route('avatar.generate', ['email' => $emailToUse, 'size' => $pixelSize * 2]);
    }
@endphp

<div {{ $attributes->merge(['class' => "avatar $class"]) }}>
    <div class="{{ $sizeClass }} rounded-full overflow-hidden">
        <img
            src="{{ $avatarUrl }}"
            alt="{{ $user?->name ?? 'Avatar' }}"
            class="object-cover w-full h-full"
            loading="lazy"
        />
    </div>
</div>

