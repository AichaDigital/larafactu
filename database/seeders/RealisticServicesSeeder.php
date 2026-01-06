<?php

declare(strict_types=1);

namespace Database\Seeders;

use AichaDigital\Larabill\Enums\BillingFrequency;
use AichaDigital\Larabill\Enums\InvoiceSerieType;
use AichaDigital\Larabill\Enums\InvoiceStatus;
use AichaDigital\Larabill\Enums\ItemType;
use AichaDigital\Larabill\Models\Article;
use AichaDigital\Larabill\Models\ArticlePrice;
use AichaDigital\Larabill\Models\Invoice;
use AichaDigital\Larabill\Models\InvoiceItem;
use AichaDigital\Larabill\Models\TaxGroup;
use AichaDigital\Larabill\Models\TaxRate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * RealisticServicesSeeder - Creates realistic services and invoices.
 *
 * Generates:
 * - Services for users (hosting+domain, domain only, VPS)
 * - Invoices from service start date until 31/01/2026
 * - Some cancelled/non-renewed services
 * - Some soft-deleted users
 *
 * Distribution:
 * - 40% hosting + domain
 * - 15% domain only
 * - 15% VPS (half with domain)
 * - 15% hosting + domain + SSL
 * - 10% no services (inactive)
 * - 5% cancelled services
 *
 * Simple seeder: No transactions, no overengineering.
 * If it fails, fix and re-run.
 *
 * @see ADR-003 User/Customer unification
 */
class RealisticServicesSeeder extends Seeder
{
    private const END_DATE = '2026-01-31';

    private ?TaxGroup $taxGroup = null;

    private ?TaxRate $taxRate = null;

    private array $hostingArticles = [];

    private array $vpsArticles = [];

    private array $domainArticles = [];

    private array $sslArticles = [];

    private int $invoiceCounter = 0;

    private int $serviceCounter = 0;

    public function run(): void
    {
        $this->command->info('Creating realistic services and invoices...');

        // Load required data
        if (! $this->loadRequiredData()) {
            return;
        }

        // Get users (excluding admin and test)
        $users = User::whereNotIn('email', ['admin@example.com', 'test@example.com'])
            ->get();

        if ($users->isEmpty()) {
            $this->command->error('No users found. Run UsersSeeder first.');

            return;
        }

        $this->command->info("Found {$users->count()} users to process");

        // Process users with service distribution
        $distribution = $this->calculateDistribution($users->count());
        $userIndex = 0;

        // Hosting + Domain (40%)
        foreach ($users->slice($userIndex, $distribution['hosting_domain']) as $user) {
            $this->createHostingWithDomain($user);
        }
        $userIndex += $distribution['hosting_domain'];

        // Domain only (15%)
        foreach ($users->slice($userIndex, $distribution['domain_only']) as $user) {
            $this->createDomainOnly($user);
        }
        $userIndex += $distribution['domain_only'];

        // VPS (15%)
        foreach ($users->slice($userIndex, $distribution['vps']) as $user) {
            $this->createVps($user, $userIndex % 2 === 0); // half with domain
        }
        $userIndex += $distribution['vps'];

        // Hosting + Domain + SSL (15%)
        foreach ($users->slice($userIndex, $distribution['hosting_ssl']) as $user) {
            $this->createHostingWithDomainAndSsl($user);
        }
        $userIndex += $distribution['hosting_ssl'];

        // Cancelled services (5%)
        foreach ($users->slice($userIndex, $distribution['cancelled']) as $user) {
            $this->createCancelledService($user);
        }
        $userIndex += $distribution['cancelled'];

        // Soft delete some users (from remaining inactive 10%)
        $inactiveStart = $userIndex;
        $softDeleteCount = (int) ceil($distribution['inactive'] * 0.3);
        foreach ($users->slice($inactiveStart, $softDeleteCount) as $user) {
            $user->delete();
        }

        $this->command->newLine();
        $this->command->info("Services created: {$this->serviceCounter}");
        $this->command->info("Invoices created: {$this->invoiceCounter}");
        $this->command->info("Soft-deleted users: {$softDeleteCount}");
    }

    /**
     * Load required data from database.
     */
    private function loadRequiredData(): bool
    {
        // Tax group and rate
        $this->taxGroup = TaxGroup::where('name', 'IVA General')->first();
        $this->taxRate = TaxRate::where('name', 'IVA General 21%')->first();

        if (! $this->taxGroup || ! $this->taxRate) {
            $this->command->error('Tax group or rate not found. Run TaxRatesSeeder and TaxGroupsSeeder first.');

            return false;
        }

        // Load articles by category
        $this->hostingArticles = Article::where('category', 'hosting')->get()->all();
        $this->vpsArticles = Article::where('category', 'vps')->get()->all();
        $this->domainArticles = Article::where('category', 'domains')->get()->all();
        $this->sslArticles = Article::where('category', 'ssl')->get()->all();

        if (empty($this->hostingArticles) || empty($this->domainArticles)) {
            $this->command->error('Articles not found. Run SpanishHostingProductsSeeder first.');

            return false;
        }

        $this->command->info('Loaded: '.count($this->hostingArticles).' hosting, '.
            count($this->vpsArticles).' VPS, '.
            count($this->domainArticles).' domains, '.
            count($this->sslArticles).' SSL');

        return true;
    }

    /**
     * Calculate user distribution.
     */
    private function calculateDistribution(int $totalUsers): array
    {
        return [
            'hosting_domain' => (int) floor($totalUsers * 0.40),
            'domain_only' => (int) floor($totalUsers * 0.15),
            'vps' => (int) floor($totalUsers * 0.15),
            'hosting_ssl' => (int) floor($totalUsers * 0.15),
            'cancelled' => (int) floor($totalUsers * 0.05),
            'inactive' => (int) floor($totalUsers * 0.10),
        ];
    }

    /**
     * Create hosting + domain service for user.
     */
    private function createHostingWithDomain(User $user): void
    {
        $hosting = $this->randomArticle($this->hostingArticles);
        $domain = $this->randomArticle($this->domainArticles);
        $startDate = $this->randomStartDate();
        $frequency = $this->randomFrequency(['YEARLY', 'QUARTERLY', 'MONTHLY']);

        $domainName = $this->generateDomainName($user);

        // Create invoices for hosting
        $this->createServiceInvoices($user, $hosting, $frequency, $startDate, $domainName);

        // Create invoices for domain (yearly)
        $this->createServiceInvoices($user, $domain, BillingFrequency::YEARLY, $startDate, $domainName);

        $this->serviceCounter += 2;
    }

    /**
     * Create domain only service for user.
     */
    private function createDomainOnly(User $user): void
    {
        $domain = $this->randomArticle($this->domainArticles);
        $startDate = $this->randomStartDate();
        $domainName = $this->generateDomainName($user);

        // Domains typically yearly, biennial, or triennial
        $frequency = $this->randomFrequency(['YEARLY', 'BIENNIAL', 'TRIENNIAL']);

        $this->createServiceInvoices($user, $domain, $frequency, $startDate, $domainName);
        $this->serviceCounter++;
    }

    /**
     * Create VPS service for user.
     */
    private function createVps(User $user, bool $withDomain): void
    {
        $vps = $this->randomArticle($this->vpsArticles);
        $startDate = $this->randomStartDate();
        $frequency = $this->randomFrequency(['MONTHLY', 'QUARTERLY', 'YEARLY']);

        $instance = 'vps-'.Str::random(6).'.server.local';

        $this->createServiceInvoices($user, $vps, $frequency, $startDate, $instance);
        $this->serviceCounter++;

        if ($withDomain && ! empty($this->domainArticles)) {
            $domain = $this->randomArticle($this->domainArticles);
            $domainName = $this->generateDomainName($user);
            $this->createServiceInvoices($user, $domain, BillingFrequency::YEARLY, $startDate, $domainName);
            $this->serviceCounter++;
        }
    }

    /**
     * Create hosting + domain + SSL service for user.
     */
    private function createHostingWithDomainAndSsl(User $user): void
    {
        $hosting = $this->randomArticle($this->hostingArticles);
        $domain = $this->randomArticle($this->domainArticles);
        $ssl = ! empty($this->sslArticles) ? $this->randomArticle($this->sslArticles) : null;

        $startDate = $this->randomStartDate();
        $frequency = $this->randomFrequency(['YEARLY', 'QUARTERLY']);
        $domainName = $this->generateDomainName($user);

        $this->createServiceInvoices($user, $hosting, $frequency, $startDate, $domainName);
        $this->createServiceInvoices($user, $domain, BillingFrequency::YEARLY, $startDate, $domainName);

        if ($ssl) {
            $this->createServiceInvoices($user, $ssl, BillingFrequency::YEARLY, $startDate, $domainName);
            $this->serviceCounter++;
        }

        $this->serviceCounter += 2;
    }

    /**
     * Create a cancelled service (partial invoices).
     */
    private function createCancelledService(User $user): void
    {
        $hosting = $this->randomArticle($this->hostingArticles);
        $domain = $this->randomArticle($this->domainArticles);

        // Service started but cancelled after 1-3 invoices
        $startDate = Carbon::create(2024, rand(1, 6), rand(1, 28));
        $domainName = $this->generateDomainName($user);

        // Create only 1-3 invoices, then stop (cancelled)
        $maxInvoices = rand(1, 3);
        $this->createServiceInvoices($user, $hosting, BillingFrequency::MONTHLY, $startDate, $domainName, $maxInvoices);
        $this->createServiceInvoices($user, $domain, BillingFrequency::YEARLY, $startDate, $domainName, 1);

        $this->serviceCounter += 2;
    }

    /**
     * Create invoices for a service from start date until end date.
     */
    private function createServiceInvoices(
        User $user,
        Article $article,
        BillingFrequency $frequency,
        Carbon $startDate,
        string $instanceId,
        ?int $maxInvoices = null
    ): void {
        $endDate = Carbon::parse(self::END_DATE);
        $currentDate = $startDate->copy();
        $invoiceCount = 0;

        // Get price for this frequency
        $price = $this->getArticlePrice($article, $frequency);
        if (! $price) {
            return;
        }

        while ($currentDate->lte($endDate)) {
            if ($maxInvoices !== null && $invoiceCount >= $maxInvoices) {
                break;
            }

            $this->createInvoice($user, $article, $frequency, $currentDate, $price, $instanceId);
            $invoiceCount++;

            // Move to next billing date
            $currentDate = $frequency->addToDate($currentDate);
        }
    }

    /**
     * Create a single invoice.
     */
    private function createInvoice(
        User $user,
        Article $article,
        BillingFrequency $frequency,
        Carbon $invoiceDate,
        int $unitPrice,
        string $instanceId
    ): void {
        $fiscalYear = $invoiceDate->year;

        // Calculate totals (base 100)
        $taxableAmount = $unitPrice; // quantity = 1
        $taxAmount = (int) round($taxableAmount * 0.21); // 21% IVA
        $totalAmount = $taxableAmount + $taxAmount;

        // Determine status based on date
        $status = $invoiceDate->lt(now())
            ? InvoiceStatus::PAID
            : InvoiceStatus::SENT;

        // Create invoice without events (bypass fiscal checks for seeder)
        $invoice = Invoice::withoutEvents(function () use (
            $user, $invoiceDate, $fiscalYear, $status, $taxableAmount, $taxAmount, $totalAmount
        ) {
            return Invoice::create([
                'id' => (string) Str::orderedUuid(),
                'fiscal_number' => $this->generateFiscalNumber($invoiceDate),
                'prefix' => 'LF',
                'serie' => InvoiceSerieType::INVOICE,
                'series_number' => $this->invoiceCounter + 1,
                'fiscal_year' => $fiscalYear,
                'invoice_date' => $invoiceDate,
                'issued_at' => $invoiceDate,
                'due_date' => $invoiceDate->copy()->addDays(30),
                'paid_at' => $status === InvoiceStatus::PAID ? $invoiceDate->copy()->addDays(rand(1, 15)) : null,
                'status' => $status,
                'user_id' => $user->id,
                'billable_user_id' => $user->id,
                'taxable_amount' => $taxableAmount,
                'total_tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'is_immutable' => false, // Set after creating item
            ]);
        });

        // Create invoice item
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => ItemType::SERVICE,
            'description' => $article->name.' - '.$instanceId,
            'internal_code' => $article->code,
            'quantity' => 100, // Base 100 = 1.00
            'unit_price' => $unitPrice,
            'taxable_amount' => $taxableAmount,
            'total_tax_amount' => $taxAmount,
            'taxes_applied' => [
                [
                    'source_rate_id' => $this->taxRate->id,
                    'name' => 'IVA 21%',
                    'rate' => 2100,
                    'amount' => $taxAmount,
                ],
            ],
            'total_amount' => $totalAmount,
            'metadata' => [
                'instance_id' => $instanceId,
                'billing_frequency' => $frequency->value,
            ],
        ]);

        // Make immutable now
        Invoice::withoutEvents(function () use ($invoice, $status, $invoiceDate) {
            $invoice->update([
                'is_immutable' => $status !== InvoiceStatus::DRAFT,
                'immutable_at' => $status !== InvoiceStatus::DRAFT ? $invoiceDate : null,
            ]);
        });

        $this->invoiceCounter++;
    }

    /**
     * Get article price for frequency.
     */
    private function getArticlePrice(Article $article, BillingFrequency $frequency): ?int
    {
        $price = ArticlePrice::where('article_id', $article->id)
            ->where('billing_frequency', $frequency)
            ->where('is_active', true)
            ->first();

        return $price?->price;
    }

    /**
     * Generate fiscal number.
     */
    private function generateFiscalNumber(Carbon $date): string
    {
        $year = $date->format('y');
        $number = str_pad((string) ($this->invoiceCounter + 1), 6, '0', STR_PAD_LEFT);

        return "LF{$year}-{$number}";
    }

    /**
     * Generate a realistic domain name for user.
     */
    private function generateDomainName(User $user): string
    {
        $baseName = Str::slug(Str::limit($user->name, 15, ''));
        $baseName = preg_replace('/[^a-z0-9-]/', '', strtolower($baseName));

        if (strlen($baseName) < 3) {
            $baseName = 'user'.rand(100, 999);
        }

        $tlds = ['.es', '.com', '.net', '.org'];
        $tld = $tlds[array_rand($tlds)];

        return $baseName.$tld;
    }

    /**
     * Get random article from array.
     */
    private function randomArticle(array $articles): Article
    {
        return $articles[array_rand($articles)];
    }

    /**
     * Get random start date (2024 or early 2025).
     */
    private function randomStartDate(): Carbon
    {
        // 60% in 2024, 40% in early 2025
        if (rand(1, 100) <= 60) {
            return Carbon::create(2024, rand(1, 12), rand(1, 28));
        }

        return Carbon::create(2025, rand(1, 6), rand(1, 28));
    }

    /**
     * Get random billing frequency from allowed list.
     */
    private function randomFrequency(array $allowed): BillingFrequency
    {
        $name = $allowed[array_rand($allowed)];

        return BillingFrequency::from(match ($name) {
            'MONTHLY' => 3,
            'QUARTERLY' => 5,
            'SEMIANNUAL' => 6,
            'YEARLY' => 7,
            'BIENNIAL' => 8,
            'TRIENNIAL' => 9,
            default => 7,
        });
    }
}
