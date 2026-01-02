<?php

declare(strict_types=1);

namespace Database\Seeders;

use AichaDigital\Larabill\Enums\BillingFrequency;
use AichaDigital\Larabill\Enums\InvoiceSerieType;
use AichaDigital\Larabill\Enums\InvoiceStatus;
use AichaDigital\Larabill\Enums\ItemType;
use AichaDigital\Larabill\Models\Article;
use AichaDigital\Larabill\Models\ArticlePrice;
use AichaDigital\Larabill\Models\CompanyFiscalConfig;
use AichaDigital\Larabill\Models\Invoice;
use AichaDigital\Larabill\Models\InvoiceItem;
use AichaDigital\Larabill\Models\InvoiceSeriesControl;
use AichaDigital\Larabill\Models\TaxGroup;
use AichaDigital\Larabill\Models\TaxRate;
use AichaDigital\Larabill\Models\UserTaxProfile;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * RealisticUsersSeeder - Creates 100 users with realistic plans and invoices.
 *
 * This seeder generates a realistic dataset for development including:
 * - 100 users (95 active + 5 cancelled)
 * - Various hosting/VPS plans with appropriate billing cycles
 * - Historical invoices from 2024-01-01 to current date
 * - Realistic invoice status distribution (85% paid, 10% issued, 5% overdue)
 *
 * Users ARE customers (ADR-003). No separate customer table.
 *
 * Plan Distribution:
 * - Hosting Annual: 40 users
 * - Hosting Monthly: 15 users
 * - Hosting Quarterly: 10 users
 * - VPS Monthly: 20 users
 * - VPS Annual: 10 users
 * - Cancelled: 5 users
 *
 * @see LOCAL_SEEDING_STRATEGY.md for full specification
 * @see GAPS_SEEDING_CONTRACTS.md for future ServiceContract migration
 */
class RealisticUsersSeeder extends Seeder
{
    /**
     * User distribution by plan type.
     */
    private const PLAN_DISTRIBUTION = [
        'hosting_annual' => 40,
        'hosting_monthly' => 15,
        'hosting_quarterly' => 10,
        'vps_monthly' => 20,
        'vps_annual' => 10,
        'cancelled' => 5,
    ];

    /**
     * Invoice status distribution (percentages).
     */
    private const STATUS_DISTRIBUTION = [
        'paid' => 85,
        'issued' => 10,
        'overdue' => 5,
    ];

    /**
     * Spanish cities for realistic addresses.
     */
    private const ES_CITIES = [
        ['city' => 'Madrid', 'state' => 'Madrid', 'zip_prefix' => '28'],
        ['city' => 'Barcelona', 'state' => 'Barcelona', 'zip_prefix' => '08'],
        ['city' => 'Valencia', 'state' => 'Valencia', 'zip_prefix' => '46'],
        ['city' => 'Sevilla', 'state' => 'Sevilla', 'zip_prefix' => '41'],
        ['city' => 'Bilbao', 'state' => 'Vizcaya', 'zip_prefix' => '48'],
        ['city' => 'Málaga', 'state' => 'Málaga', 'zip_prefix' => '29'],
        ['city' => 'Zaragoza', 'state' => 'Zaragoza', 'zip_prefix' => '50'],
        ['city' => 'Alicante', 'state' => 'Alicante', 'zip_prefix' => '03'],
        ['city' => 'Murcia', 'state' => 'Murcia', 'zip_prefix' => '30'],
        ['city' => 'Granada', 'state' => 'Granada', 'zip_prefix' => '18'],
    ];

    /**
     * Entity types for tax profiles.
     */
    private const ENTITY_TYPES = [
        'individuals' => ['INDIVIDUAL', 'SELF_EMPLOYED'],
        'companies' => ['LIMITED_COMPANY', 'PUBLIC_LIMITED_COMPANY'],
    ];

    private \Faker\Generator $faker;

    private ?CompanyFiscalConfig $companyConfig = null;

    private ?TaxGroup $taxGroup = null;

    private ?TaxRate $taxRate = null;

    /** @var Collection<int, Article> */
    private Collection $hostingArticles;

    /** @var Collection<int, Article> */
    private Collection $vpsArticles;

    /** @var Collection<int, Article> */
    private Collection $domainArticles;

    /** @var array<int, InvoiceSeriesControl> */
    private array $invoiceSeries = [];

    /** @var array<int, int> */
    private array $seriesCounters = [];

    private array $usedEmails = [];

    private array $usedTaxIds = [];

    private int $totalInvoices = 0;

    /**
     * Run the seeder.
     */
    public function run(): void
    {
        $this->faker = Faker::create('es_ES');

        $this->command->info('Creating realistic users with plans and invoices...');

        // Load required data
        if (! $this->loadRequiredData()) {
            return;
        }

        // Load invoice series for numbering
        $this->loadInvoiceSeries();

        $userCount = 0;

        DB::transaction(function () use (&$userCount): void {
            foreach (self::PLAN_DISTRIBUTION as $planType => $count) {
                for ($i = 0; $i < $count; $i++) {
                    $this->createUserWithPlan($planType);
                    $userCount++;

                    if ($userCount % 25 === 0) {
                        $this->command->info("  Created {$userCount} users...");
                    }
                }
            }
        });

        // Update series counters in DB
        $this->updateSeriesCounters();

        $this->command->newLine();
        $this->command->info('Realistic users seeder complete!');
        $this->command->line("  Users created: {$userCount}");
        $this->command->line("  Invoices generated: {$this->totalInvoices}");
    }

    /**
     * Load required data from database.
     */
    private function loadRequiredData(): bool
    {
        // Company fiscal config
        $this->companyConfig = CompanyFiscalConfig::first();
        if (! $this->companyConfig) {
            $this->command->error('No CompanyFiscalConfig found. Run DevelopmentSeeder first.');

            return false;
        }

        // Tax group and rate
        $this->taxGroup = TaxGroup::where('name', 'IVA General')->first();
        $this->taxRate = TaxRate::where('rate', 2100)->first(); // 21% IVA

        // Load articles by category
        $this->hostingArticles = Article::where('category', 'hosting')->get();
        $this->vpsArticles = Article::where('category', 'vps')->get();
        $this->domainArticles = Article::where('category', 'domains')->get();

        if ($this->hostingArticles->isEmpty() || $this->vpsArticles->isEmpty()) {
            $this->command->error('No hosting/VPS articles found. Run SpanishHostingProductsSeeder first.');

            return false;
        }

        return true;
    }

    /**
     * Load invoice series for each year.
     */
    private function loadInvoiceSeries(): void
    {
        foreach ([2024, 2025, 2026] as $year) {
            $series = InvoiceSeriesControl::where('fiscal_year', $year)
                ->where('serie', InvoiceSerieType::INVOICE->value)
                ->where('prefix', 'LF')
                ->first();

            if ($series) {
                $this->invoiceSeries[$year] = $series;
                $this->seriesCounters[$year] = $series->last_number;
            }
        }
    }

    /**
     * Update series counters after seeding.
     */
    private function updateSeriesCounters(): void
    {
        foreach ($this->invoiceSeries as $year => $series) {
            if (isset($this->seriesCounters[$year]) && $this->seriesCounters[$year] > $series->last_number) {
                $series->update([
                    'last_number' => $this->seriesCounters[$year],
                    'last_used_at' => now(),
                ]);
            }
        }
    }

    /**
     * Create a user with the specified plan type.
     */
    private function createUserWithPlan(string $planType): void
    {
        $isCancelled = $planType === 'cancelled';

        // Determine signup date range
        $signupStart = Carbon::create(2024, 1, 1);
        $signupEnd = $isCancelled
            ? Carbon::create(2024, 12, 31) // Cancelled users signed up in 2024
            : Carbon::create(2025, 6, 30);  // Active users can sign up until mid-2025

        $signupDate = $this->faker->dateTimeBetween($signupStart, $signupEnd);
        $signupCarbon = Carbon::instance($signupDate)->startOfDay();

        // Create user
        $user = $this->createUser($signupCarbon);

        // Create tax profile
        $taxProfile = $this->createUserTaxProfile($user, $signupCarbon);

        // Update user with current tax profile
        $user->update(['current_tax_profile_id' => $taxProfile->id]);

        // Determine cancellation date if applicable
        $cancelledAt = null;
        if ($isCancelled) {
            // Cancel 2-8 months after signup
            $cancelledAt = $signupCarbon->copy()->addMonths($this->faker->numberBetween(2, 8));
            if ($cancelledAt->greaterThan(now())) {
                $cancelledAt = now()->subDays($this->faker->numberBetween(1, 30));
            }
        }

        // If cancelled, use a random active plan type for the subscription
        $actualPlanType = $isCancelled
            ? $this->faker->randomElement(['hosting_monthly', 'vps_monthly', 'hosting_annual'])
            : $planType;

        // Generate invoices based on plan
        $this->generateInvoicesForUser($user, $taxProfile, $actualPlanType, $signupCarbon, $cancelledAt);
    }

    /**
     * Create a user.
     */
    private function createUser(Carbon $signupDate): User
    {
        $isCompany = $this->faker->boolean(40); // 40% companies

        if ($isCompany) {
            $name = $this->faker->company().' '.$this->faker->randomElement(['S.L.', 'S.A.']);
            $emailPrefix = Str::slug(Str::limit($name, 20, ''));
        } else {
            $name = $this->faker->name();
            $emailPrefix = Str::slug($name);
        }

        $email = $this->generateUniqueEmail($emailPrefix);

        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'email_verified_at' => $signupDate,
        ]);
    }

    /**
     * Create tax profile for user.
     */
    private function createUserTaxProfile(User $user, Carbon $validFrom): UserTaxProfile
    {
        $isCompany = $this->faker->boolean(40);
        $location = $this->faker->randomElement(self::ES_CITIES);

        $entityType = $isCompany
            ? $this->faker->randomElement(self::ENTITY_TYPES['companies'])
            : $this->faker->randomElement(self::ENTITY_TYPES['individuals']);

        $taxId = $isCompany
            ? $this->generateSpanishCIF($entityType)
            : $this->generateSpanishNIF();

        return UserTaxProfile::create([
            'owner_user_id' => $user->id,
            'fiscal_name' => $user->name,
            'tax_id' => $taxId,
            'legal_entity_type_code' => $entityType,
            'address' => $this->faker->streetAddress(),
            'city' => $location['city'],
            'state' => $location['state'],
            'zip_code' => $location['zip_prefix'].str_pad((string) $this->faker->numberBetween(0, 999), 3, '0', STR_PAD_LEFT),
            'country_code' => 'ES',
            'is_company' => $isCompany,
            'is_eu_vat_registered' => false,
            'is_exempt_vat' => false,
            'valid_from' => $validFrom,
            'valid_until' => null,
            'is_active' => true,
            'notes' => "Seeder - {$entityType}",
        ]);
    }

    /**
     * Generate invoices for a user based on their plan.
     */
    private function generateInvoicesForUser(
        User $user,
        UserTaxProfile $taxProfile,
        string $planType,
        Carbon $signupDate,
        ?Carbon $cancelledAt
    ): void {
        // Determine billing frequency
        $frequency = match ($planType) {
            'hosting_annual', 'vps_annual' => BillingFrequency::YEARLY,
            'hosting_quarterly' => BillingFrequency::QUARTERLY,
            default => BillingFrequency::MONTHLY,
        };

        // Determine main product
        $isVps = str_starts_with($planType, 'vps');
        $mainArticle = $isVps
            ? $this->vpsArticles->random()
            : $this->hostingArticles->random();

        // For hosting, also include a domain on first invoice
        $includeDomain = ! $isVps && $this->domainArticles->isNotEmpty();
        $domainArticle = $includeDomain ? $this->domainArticles->random() : null;

        // Calculate invoice dates
        $currentDate = $signupDate->copy();
        $endDate = $cancelledAt ?? now();
        $invoiceCount = 0;

        while ($currentDate->lessThanOrEqualTo($endDate)) {
            $isFirstInvoice = $invoiceCount === 0;

            // Create invoice
            $this->createInvoice(
                $user,
                $taxProfile,
                $currentDate,
                $mainArticle,
                $frequency,
                $domainArticle,
                $isFirstInvoice
            );

            $invoiceCount++;
            $this->totalInvoices++;

            // Advance to next billing date
            $currentDate = $this->getNextBillingDate($currentDate, $frequency);

            // Check domain renewal (yearly, separate from hosting)
            if ($includeDomain && $invoiceCount > 0) {
                $domainRenewalDate = $signupDate->copy()->addYear();
                while ($domainRenewalDate->lessThanOrEqualTo($endDate) && $domainRenewalDate->lessThanOrEqualTo($currentDate)) {
                    // Domain renewal is always yearly
                    $this->createDomainRenewalInvoice($user, $taxProfile, $domainRenewalDate, $domainArticle);
                    $this->totalInvoices++;
                    $domainRenewalDate->addYear();
                }
            }
        }
    }

    /**
     * Create an invoice.
     */
    private function createInvoice(
        User $user,
        UserTaxProfile $taxProfile,
        Carbon $invoiceDate,
        Article $mainArticle,
        BillingFrequency $frequency,
        ?Article $domainArticle,
        bool $includeNewDomain
    ): Invoice {
        $fiscalYear = $invoiceDate->year;
        $seriesNumber = $this->getNextSeriesNumber($fiscalYear);
        $shortYear = substr((string) $fiscalYear, -2);

        // Determine status
        $status = $this->determineInvoiceStatus($invoiceDate);

        // Calculate due date (30 days from invoice date)
        $dueDate = $invoiceDate->copy()->addDays(30);

        // Determine paid_at for PAID invoices
        $paidAt = null;
        if ($status === InvoiceStatus::PAID) {
            $paidAt = $invoiceDate->copy()->addDays($this->faker->numberBetween(1, 25));
        }

        // Create invoice without fiscal snapshots (seeder mode)
        // Use withoutEvents to bypass fiscal integrity checks in boot
        $invoice = Invoice::withoutEvents(function () use (
            $shortYear,
            $seriesNumber,
            $fiscalYear,
            $invoiceDate,
            $dueDate,
            $status,
            $paidAt,
            $user,
            $taxProfile,
            $mainArticle
        ) {
            return Invoice::create([
                'id' => (string) Str::orderedUuid(),
                'fiscal_number' => "LF{$shortYear}-".str_pad((string) $seriesNumber, 5, '0', STR_PAD_LEFT),
                'prefix' => 'LF',
                'serie' => InvoiceSerieType::INVOICE,
                'series_number' => $seriesNumber,
                'fiscal_year' => $fiscalYear,
                'invoice_date' => $invoiceDate,
                'issued_at' => $invoiceDate,
                'due_date' => $dueDate,
                'status' => $status,
                'paid_at' => $paidAt,
                'user_id' => $user->id,
                'billable_user_id' => $user->id,
                'company_fiscal_config_id' => $this->companyConfig->id,
                'user_tax_profile_id' => $taxProfile->id,
                'is_immutable' => false, // Set after calculating totals
                'immutable_at' => null,
                'is_roi_taxed' => false,
                'notes' => "Generada por seeder - {$mainArticle->name}",
            ]);
        });

        // Create invoice items
        $items = [];

        // Main service item
        $items[] = $this->createInvoiceItem($invoice, $mainArticle, $frequency, $invoiceDate);

        // Domain item on first invoice
        if ($includeNewDomain && $domainArticle) {
            // Initial domain registration (1 or 2 years)
            $domainYears = $this->faker->randomElement([1, 2]);
            $domainFrequency = $domainYears === 1 ? BillingFrequency::YEARLY : BillingFrequency::BIENNIAL;
            $items[] = $this->createInvoiceItem($invoice, $domainArticle, $domainFrequency, $invoiceDate);
        }

        // Calculate totals
        $taxableAmount = array_sum(array_column($items, 'taxable'));
        $taxAmount = array_sum(array_column($items, 'tax'));
        $totalAmount = array_sum(array_column($items, 'total'));

        Invoice::withoutEvents(function () use ($invoice, $taxableAmount, $taxAmount, $totalAmount, $status, $invoiceDate): void {
            $invoice->update([
                'taxable_amount' => $taxableAmount,
                'total_tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'is_immutable' => $status !== InvoiceStatus::DRAFT,
                'immutable_at' => $status !== InvoiceStatus::DRAFT ? $invoiceDate : null,
            ]);
        });

        return $invoice;
    }

    /**
     * Create domain renewal invoice.
     */
    private function createDomainRenewalInvoice(
        User $user,
        UserTaxProfile $taxProfile,
        Carbon $invoiceDate,
        Article $domainArticle
    ): Invoice {
        $fiscalYear = $invoiceDate->year;
        $seriesNumber = $this->getNextSeriesNumber($fiscalYear);
        $shortYear = substr((string) $fiscalYear, -2);

        $status = $this->determineInvoiceStatus($invoiceDate);
        $dueDate = $invoiceDate->copy()->addDays(30);

        $paidAt = null;
        if ($status === InvoiceStatus::PAID) {
            $paidAt = $invoiceDate->copy()->addDays($this->faker->numberBetween(1, 25));
        }

        $invoice = Invoice::withoutEvents(function () use (
            $shortYear,
            $seriesNumber,
            $fiscalYear,
            $invoiceDate,
            $dueDate,
            $status,
            $paidAt,
            $user,
            $taxProfile,
            $domainArticle
        ) {
            return Invoice::create([
                'id' => (string) Str::orderedUuid(),
                'fiscal_number' => "LF{$shortYear}-".str_pad((string) $seriesNumber, 5, '0', STR_PAD_LEFT),
                'prefix' => 'LF',
                'serie' => InvoiceSerieType::INVOICE,
                'series_number' => $seriesNumber,
                'fiscal_year' => $fiscalYear,
                'invoice_date' => $invoiceDate,
                'issued_at' => $invoiceDate,
                'due_date' => $dueDate,
                'status' => $status,
                'paid_at' => $paidAt,
                'user_id' => $user->id,
                'billable_user_id' => $user->id,
                'company_fiscal_config_id' => $this->companyConfig->id,
                'user_tax_profile_id' => $taxProfile->id,
                'is_immutable' => false, // Set after calculating totals
                'immutable_at' => null,
                'is_roi_taxed' => false,
                'notes' => "Renovacion dominio - {$domainArticle->name}",
            ]);
        });

        // Domain renewal is always yearly
        $item = $this->createInvoiceItem($invoice, $domainArticle, BillingFrequency::YEARLY, $invoiceDate);

        Invoice::withoutEvents(function () use ($invoice, $item, $status, $invoiceDate): void {
            $invoice->update([
                'taxable_amount' => $item['taxable'],
                'total_tax_amount' => $item['tax'],
                'total_amount' => $item['total'],
                'is_immutable' => $status !== InvoiceStatus::DRAFT,
                'immutable_at' => $status !== InvoiceStatus::DRAFT ? $invoiceDate : null,
            ]);
        });

        return $invoice;
    }

    /**
     * Create an invoice item.
     *
     * @return array{taxable: int, tax: int, total: int}
     */
    private function createInvoiceItem(
        Invoice $invoice,
        Article $article,
        BillingFrequency $frequency,
        Carbon $invoiceDate
    ): array {
        // Get price for frequency
        $articlePrice = ArticlePrice::where('article_id', $article->id)
            ->where('billing_frequency', $frequency->value)
            ->where('is_active', true)
            ->first();

        $unitPrice = $articlePrice?->price ?? 1000; // Default 10 EUR if no price found

        // Calculate tax (21% IVA)
        $taxRate = 2100; // 21% in base 100
        $taxableAmount = $unitPrice; // Quantity is 1
        $taxAmount = (int) round($taxableAmount * $taxRate / 10000);
        $totalAmount = $taxableAmount + $taxAmount;

        // Service period
        $serviceDateFrom = $invoiceDate->copy();
        $serviceDateTo = $this->getNextBillingDate($invoiceDate, $frequency)->subDay();

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => ItemType::SERVICE,
            'description' => $this->getItemDescription($article, $frequency),
            'internal_code' => $article->code,
            'quantity' => 100, // 1.00 in base 100
            'unit_measure_id' => $article->unit_measure_id,
            'unit_price' => $unitPrice,
            'taxable_amount' => $taxableAmount,
            'total_tax_amount' => $taxAmount,
            'taxes_applied' => $this->taxRate ? [
                [
                    'source_rate_id' => $this->taxRate->id,
                    'name' => 'IVA 21%',
                    'rate' => $taxRate,
                    'amount' => $taxAmount,
                ],
            ] : [],
            'total_amount' => $totalAmount,
            'service_date_from' => $serviceDateFrom,
            'service_date_to' => $serviceDateTo,
            'metadata' => [
                'frequency' => $frequency->value,
                'seeded' => true,
            ],
        ]);

        return [
            'taxable' => $taxableAmount,
            'tax' => $taxAmount,
            'total' => $totalAmount,
        ];
    }

    /**
     * Get next billing date based on frequency.
     */
    private function getNextBillingDate(Carbon $currentDate, BillingFrequency $frequency): Carbon
    {
        // Use the enum's built-in addToDate method
        return $frequency->addToDate($currentDate);
    }

    /**
     * Determine invoice status based on date and distribution.
     */
    private function determineInvoiceStatus(Carbon $invoiceDate): InvoiceStatus
    {
        // Future invoices are always issued/pending
        if ($invoiceDate->greaterThan(now())) {
            return InvoiceStatus::SENT;
        }

        // Recent invoices (last 30 days) can be issued or overdue
        $daysSinceInvoice = $invoiceDate->diffInDays(now());

        if ($daysSinceInvoice < 30) {
            // 85% paid, 15% still pending
            return $this->faker->boolean(85) ? InvoiceStatus::PAID : InvoiceStatus::SENT;
        }

        // Older invoices follow the full distribution
        $rand = $this->faker->numberBetween(1, 100);

        if ($rand <= self::STATUS_DISTRIBUTION['paid']) {
            return InvoiceStatus::PAID;
        }

        if ($rand <= self::STATUS_DISTRIBUTION['paid'] + self::STATUS_DISTRIBUTION['issued']) {
            return InvoiceStatus::SENT;
        }

        return InvoiceStatus::OVERDUE;
    }

    /**
     * Get description for invoice item.
     */
    private function getItemDescription(Article $article, BillingFrequency $frequency): string
    {
        $periodLabel = match ($frequency) {
            BillingFrequency::MONTHLY => 'mensual',
            BillingFrequency::QUARTERLY => 'trimestral',
            BillingFrequency::SEMIANNUAL => 'semestral',
            BillingFrequency::YEARLY => 'anual',
            BillingFrequency::BIENNIAL => '2 años',
            BillingFrequency::TRIENNIAL => '3 años',
            default => $frequency->label(),
        };

        return "{$article->name} ({$periodLabel})";
    }

    /**
     * Get next series number for a fiscal year.
     */
    private function getNextSeriesNumber(int $year): int
    {
        if (! isset($this->seriesCounters[$year])) {
            // If no series exists for this year, start from 1
            $this->seriesCounters[$year] = 0;
        }

        $this->seriesCounters[$year]++;

        return $this->seriesCounters[$year];
    }

    /**
     * Generate unique email.
     */
    private function generateUniqueEmail(string $prefix): string
    {
        $baseEmail = strtolower(preg_replace('/[^a-z0-9]/', '', $prefix));
        $baseEmail = Str::limit($baseEmail, 30, '');

        $email = "{$baseEmail}@example.com";
        $counter = 1;

        while (in_array($email, $this->usedEmails, true)) {
            $email = "{$baseEmail}{$counter}@example.com";
            $counter++;
        }

        $this->usedEmails[] = $email;

        return $email;
    }

    /**
     * Generate Spanish NIF.
     */
    private function generateSpanishNIF(): string
    {
        $letters = 'TRWAGMYFPDXBNJZSQVHLCKE';

        do {
            $number = $this->faker->numberBetween(10000000, 99999999);
            $letter = $letters[$number % 23];
            $nif = $number.$letter;
        } while (in_array($nif, $this->usedTaxIds, true));

        $this->usedTaxIds[] = $nif;

        return $nif;
    }

    /**
     * Generate Spanish CIF.
     */
    private function generateSpanishCIF(string $entityCode): string
    {
        $prefixes = match ($entityCode) {
            'LIMITED_COMPANY' => ['B'],
            'PUBLIC_LIMITED_COMPANY' => ['A'],
            default => ['B'],
        };

        $prefix = $this->faker->randomElement($prefixes);

        do {
            $digits = str_pad((string) $this->faker->numberBetween(1000000, 9999999), 7, '0', STR_PAD_LEFT);
            $control = $this->calculateCifControl($prefix.$digits);
            $cif = $prefix.$digits.$control;
        } while (in_array($cif, $this->usedTaxIds, true));

        $this->usedTaxIds[] = $cif;

        return $cif;
    }

    /**
     * Calculate CIF control character.
     */
    private function calculateCifControl(string $cifWithoutControl): string
    {
        $digits = substr($cifWithoutControl, 1);
        $sumEven = 0;
        $sumOdd = 0;

        for ($i = 0; $i < 7; $i++) {
            $digit = (int) $digits[$i];
            if ($i % 2 === 0) {
                $doubled = $digit * 2;
                $sumOdd += (int) floor($doubled / 10) + ($doubled % 10);
            } else {
                $sumEven += $digit;
            }
        }

        $total = $sumEven + $sumOdd;
        $control = (10 - ($total % 10)) % 10;

        return (string) $control;
    }
}
