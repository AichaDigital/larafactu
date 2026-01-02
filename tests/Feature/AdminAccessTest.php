<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Set environment to production for these tests
    config(['app.env' => 'production']);
});

it('allows admin access with exact email match', function () {
    config(['app.admin_emails' => 'admin@example.com,manager@company.com']);

    $user = User::factory()->create(['email' => 'admin@example.com']);

    expect($user->isAdmin())->toBeTrue();
});

it('denies admin access with email not in list', function () {
    config(['app.admin_emails' => 'admin@example.com']);

    $user = User::factory()->create(['email' => 'hacker@malicious.com']);

    expect($user->isAdmin())->toBeFalse();
});

it('allows admin access with domain match', function () {
    config(['app.admin_domains' => '@example.com,@company.com']);

    $user = User::factory()->create(['email' => 'anyone@example.com']);

    expect($user->isAdmin())->toBeTrue();
});

it('denies admin access with domain not in list', function () {
    config(['app.admin_domains' => '@example.com']);

    $user = User::factory()->create(['email' => 'user@otherdomain.com']);

    expect($user->isAdmin())->toBeFalse();
});

it('allows admin access with either email or domain match', function () {
    config([
        'app.admin_emails' => 'specific@example.com',
        'app.admin_domains' => '@company.com',
    ]);

    $userByEmail = User::factory()->create(['email' => 'specific@example.com']);
    $userByDomain = User::factory()->create(['email' => 'anyone@company.com']);

    expect($userByEmail->isAdmin())->toBeTrue();
    expect($userByDomain->isAdmin())->toBeTrue();
});

it('denies admin access when no config is set', function () {
    config(['app.admin_emails' => '', 'app.admin_domains' => '']);

    $user = User::factory()->create(['email' => 'user@example.com']);

    expect($user->isAdmin())->toBeFalse();
});

it('handles whitespace in config correctly', function () {
    config(['app.admin_emails' => ' admin@example.com , manager@company.com ']);

    $user = User::factory()->create(['email' => 'admin@example.com']);

    expect($user->isAdmin())->toBeTrue();
});
