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

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/users', function () {
        return view('admin.users');
    })->name('users');
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
