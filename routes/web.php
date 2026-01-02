<?php

use App\Livewire\Articles\ArticleCreate;
use App\Livewire\Articles\ArticleEdit;
use App\Livewire\Articles\ArticleList;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Customers\CustomerCreate;
use App\Livewire\Customers\CustomerEdit;
use App\Livewire\Customers\CustomerList;
use App\Livewire\Invoices\InvoiceCreate;
use App\Livewire\Invoices\InvoiceEdit;
use App\Livewire\Invoices\InvoiceList;
use App\Livewire\Invoices\InvoiceShow;
use App\Livewire\Profile\ProfileEdit;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', Login::class)->name('login')->middleware('guest');
Route::get('/register', Register::class)->name('register')->middleware('guest');
Route::get('/forgot-password', ForgotPassword::class)->name('password.request')->middleware('guest');
Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset')->middleware('guest');

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile
    Route::get('/profile', ProfileEdit::class)->name('profile');

    // Invoices
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', InvoiceList::class)->name('index');
        Route::get('/create', InvoiceCreate::class)->name('create');
        Route::get('/{invoice}', InvoiceShow::class)->name('show');
        Route::get('/{invoice}/edit', InvoiceEdit::class)->name('edit');
        Route::get('/{invoice}/pdf', function (\AichaDigital\Larabill\Models\Invoice $invoice) {
            // Generate PDF if not exists
            $pdfPath = $invoice->getPDFPath();
            if (! $pdfPath) {
                $result = $invoice->generatePDF();
                if (! $result['success']) {
                    abort(500, 'Error generating PDF');
                }
                $pdfPath = $result['pdf_path'];
            }

            $filename = $invoice->fiscal_number.'.pdf';
            $disposition = request()->has('download') ? 'attachment' : 'inline';

            return response()->file($pdfPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
            ]);
        })->name('pdf');
    });

    // Customers
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', CustomerList::class)->name('index');
        Route::get('/create', CustomerCreate::class)->name('create');
        Route::get('/{customer}/edit', CustomerEdit::class)->name('edit');
    });

    // Articles
    Route::prefix('articles')->name('articles.')->group(function () {
        Route::get('/', ArticleList::class)->name('index');
        Route::get('/create', ArticleCreate::class)->name('create');
        Route::get('/{article}/edit', ArticleEdit::class)->name('edit');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin', 'block-impersonation'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Users management
    Route::get('/users', App\Livewire\Admin\UserList::class)->name('users');
    Route::get('/users/create', App\Livewire\Admin\UserCreate::class)->name('users.create');
    Route::get('/users/{user}/edit', App\Livewire\Admin\UserEdit::class)->name('users.edit');

    // Fiscal Configuration management
    Route::get('/fiscal-configs', App\Livewire\Admin\FiscalConfigList::class)->name('fiscal-configs');
    Route::get('/fiscal-configs/create', App\Livewire\Admin\FiscalConfigCreate::class)->name('fiscal-configs.create');
    Route::get('/fiscal-configs/{fiscalConfig}', App\Livewire\Admin\FiscalConfigShow::class)->name('fiscal-configs.show');
    Route::get('/fiscal-configs/{fiscalConfig}/edit', App\Livewire\Admin\FiscalConfigEdit::class)->name('fiscal-configs.edit');

    // Invoice Series management
    Route::get('/invoice-series', App\Livewire\Admin\InvoiceSeriesList::class)->name('invoice-series');
    Route::get('/invoice-series/create', App\Livewire\Admin\InvoiceSeriesCreate::class)->name('invoice-series.create');
    Route::get('/invoice-series/{invoiceSeries}', App\Livewire\Admin\InvoiceSeriesShow::class)->name('invoice-series.show');
    Route::get('/invoice-series/{invoiceSeries}/edit', App\Livewire\Admin\InvoiceSeriesEdit::class)->name('invoice-series.edit');

    // Impersonation routes (stop must be before {user} to avoid route conflict)
    Route::post('/impersonate-stop', function () {
        app(App\Services\ImpersonationService::class)->stop();

        return redirect()->route('admin.dashboard')
            ->with('success', 'Has vuelto a tu cuenta de administrador.');
    })->name('impersonate.stop')->withoutMiddleware('admin');

    Route::post('/impersonate/{user}', function (App\Models\User $user) {
        $service = app(App\Services\ImpersonationService::class);

        if (! $service->start($user)) {
            abort(403, 'No puedes impersonar a este usuario.');
        }

        return redirect()->route('dashboard')
            ->with('success', "Ahora estas viendo la aplicacion como {$user->name}");
    })->name('impersonate.start');
});

/*
|--------------------------------------------------------------------------
| API Routes (for theme switching, etc.)
|--------------------------------------------------------------------------
*/

Route::post('/api/theme', function () {
    $theme = request()->input('theme');
    if (! \App\Models\UserPreference::isValidTheme($theme)) {
        return response()->json(['error' => 'Invalid theme'], 400);
    }

    session(['theme' => $theme]);

    // Persist to database if authenticated
    if (auth()->check()) {
        $preferences = auth()->user()->getPreferences();
        $preferences->update(['theme' => $theme]);
    }

    return response()->json(['success' => true]);
})->name('api.theme');
