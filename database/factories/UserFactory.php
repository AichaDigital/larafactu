<?php

namespace Database\Factories;

use AichaDigital\Larabill\Enums\UserRelationshipType;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'parent_user_id' => null,
            'relationship_type' => UserRelationshipType::DIRECT, // DEPRECATED by ADR-004
            'display_name' => null,
            'legal_entity_type_code' => null,
            // ADR-004: Authorization defaults
            'user_type' => UserType::CUSTOMER, // Default to customer
            'is_active' => true,
            'suspended_at' => null,
            'is_superadmin' => false,
        ];
    }

    // ========================================
    // ADR-003 RELATIONSHIP STATES
    // ========================================

    /**
     * Create a delegated user under a parent user.
     */
    public function delegatedOf(User $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_user_id' => $parent->id,
            'relationship_type' => UserRelationshipType::DELEGATED,
        ]);
    }

    /**
     * Create a direct user (explicit state, default behavior).
     */
    public function direct(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_user_id' => null,
            'relationship_type' => UserRelationshipType::DIRECT,
        ]);
    }

    /**
     * Set a display name (commercial name for billing).
     */
    public function withDisplayName(?string $displayName = null): static
    {
        return $this->state(fn (array $attributes) => [
            'display_name' => $displayName ?? fake()->company(),
        ]);
    }

    /**
     * Set legal entity type code.
     */
    public function withLegalEntityType(string $code): static
    {
        return $this->state(fn (array $attributes) => [
            'legal_entity_type_code' => $code,
        ]);
    }

    /**
     * Spanish company (ES - Spain)
     */
    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->company().' S.L.',
            'email' => fake()->unique()->companyEmail(),
        ]);
    }

    /**
     * EU Intra-community company (ROI - Reverse Charge Operator)
     */
    public function euRoi(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->company().' GmbH',
            'email' => fake()->unique()->companyEmail(),
        ]);
    }

    /**
     * EU Intra-community company (Non-ROI)
     */
    public function euNonRoi(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->company().' SAS',
            'email' => fake()->unique()->companyEmail(),
        ]);
    }

    /**
     * Non-EU company (outside EU)
     */
    public function nonEu(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->company().' Inc.',
            'email' => fake()->unique()->companyEmail(),
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    // ========================================
    // ADR-004 USER TYPE STATES
    // ========================================

    /**
     * Create a staff user (can access admin panel).
     */
    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => UserType::STAFF,
        ]);
    }

    /**
     * Create a customer user (default, explicit state).
     */
    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => UserType::CUSTOMER,
        ]);
    }

    /**
     * Create a delegate user.
     */
    public function delegate(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => UserType::DELEGATE,
        ]);
    }

    /**
     * Create a superadmin user (bypasses all authorization).
     */
    public function superadmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => UserType::STAFF,
            'is_superadmin' => true,
        ]);
    }

    /**
     * Create an inactive/disabled user.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a suspended user.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'suspended_at' => now(),
        ]);
    }
}
