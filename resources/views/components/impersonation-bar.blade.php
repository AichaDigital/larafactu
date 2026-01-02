@php
    $impersonationService = app(\App\Services\ImpersonationService::class);
    $isImpersonating = $impersonationService->isImpersonating();
    $impersonatedUser = $isImpersonating ? $impersonationService->getImpersonatedUser() : null;
@endphp

@if ($isImpersonating && $impersonatedUser)
    <div class="bg-warning text-warning-content">
        <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <span class="font-medium">
                    Estas viendo la aplicacion como <strong>{{ $impersonatedUser->name }}</strong>
                </span>
            </div>
            <form action="{{ route('admin.impersonate.stop') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-ghost gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" x2="9" y1="12" y2="12"/>
                    </svg>
                    Volver a mi cuenta
                </button>
            </form>
        </div>
    </div>
@endif
