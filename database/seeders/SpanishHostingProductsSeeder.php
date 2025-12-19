<?php

declare(strict_types=1);

namespace Database\Seeders;

use AichaDigital\Larabill\Enums\BillingFrequency;
use AichaDigital\Larabill\Enums\ItemType;
use AichaDigital\Larabill\Models\Article;
use AichaDigital\Larabill\Models\ArticlePrice;
use AichaDigital\Larabill\Models\TaxGroup;
use AichaDigital\Larabill\Models\UnitMeasure;
use Illuminate\Database\Seeder;

/**
 * SpanishHostingProductsSeeder - Creates hosting products for Spanish market.
 *
 * Products:
 * - Hosting: ESTANDAR, PRO, EMPRESA (monthly, quarterly, yearly)
 * - VPS: ALFA, BETA, GAMMA (monthly, quarterly, yearly)
 * - Domains: .es, .com, .net, .org, .cat, .eu (yearly registration)
 * - SSL Certificates: Basic, Comodo, Wildcard variants (yearly)
 *
 * Pricing:
 * - Monthly: base price
 * - Quarterly: 3x monthly (no discount)
 * - Yearly: 11x monthly (1 month free)
 *
 * All prices stored in cents (Base100Int).
 *
 * @see ADR-001 for fiscal configuration
 */
class SpanishHostingProductsSeeder extends Seeder
{
    /**
     * Hosting plans configuration.
     * Prices in euros (converted to cents in seeder).
     */
    private const HOSTING_PLANS = [
        'ESTANDAR' => [
            'name' => 'Hosting Estándar',
            'description' => 'Hosting compartido básico. 10GB SSD, 1 dominio, Email ilimitado.',
            'monthly_price' => 6.00,
        ],
        'PRO' => [
            'name' => 'Hosting Pro',
            'description' => 'Hosting compartido profesional. 50GB SSD, 5 dominios, SSL incluido.',
            'monthly_price' => 10.00,
        ],
        'EMPRESA' => [
            'name' => 'Hosting Empresa',
            'description' => 'Hosting empresarial. 100GB SSD, dominios ilimitados, SSL, CDN.',
            'monthly_price' => 20.00,
        ],
    ];

    /**
     * VPS plans configuration.
     */
    private const VPS_PLANS = [
        'ALFA' => [
            'name' => 'VPS Alfa',
            'description' => 'VPS básico. 2 vCPU, 4GB RAM, 80GB SSD, 2TB transferencia.',
            'monthly_price' => 20.00,
        ],
        'BETA' => [
            'name' => 'VPS Beta',
            'description' => 'VPS profesional. 4 vCPU, 8GB RAM, 160GB SSD, 4TB transferencia.',
            'monthly_price' => 30.00,
        ],
        'GAMMA' => [
            'name' => 'VPS Gamma',
            'description' => 'VPS empresarial. 8 vCPU, 16GB RAM, 320GB SSD, 8TB transferencia.',
            'monthly_price' => 50.00,
        ],
    ];

    /**
     * Domain TLDs with yearly prices.
     */
    private const DOMAINS = [
        '.es' => [
            'name' => 'Dominio .es',
            'description' => 'Registro de dominio .es (España). Incluye gestión DNS.',
            'yearly_price' => 9.00,
        ],
        '.com' => [
            'name' => 'Dominio .com',
            'description' => 'Registro de dominio .com (comercial). Incluye gestión DNS.',
            'yearly_price' => 12.00,
        ],
        '.net' => [
            'name' => 'Dominio .net',
            'description' => 'Registro de dominio .net (network). Incluye gestión DNS.',
            'yearly_price' => 12.00,
        ],
        '.org' => [
            'name' => 'Dominio .org',
            'description' => 'Registro de dominio .org (organizaciones). Incluye gestión DNS.',
            'yearly_price' => 14.00,
        ],
        '.cat' => [
            'name' => 'Dominio .cat',
            'description' => 'Registro de dominio .cat (Cataluña). Incluye gestión DNS.',
            'yearly_price' => 12.00,
        ],
        '.eu' => [
            'name' => 'Dominio .eu',
            'description' => 'Registro de dominio .eu (Europa). Incluye gestión DNS.',
            'yearly_price' => 12.00,
        ],
    ];

    /**
     * SSL Certificates with yearly prices.
     */
    private const SSL_CERTIFICATES = [
        'SSL_BASIC' => [
            'name' => 'Certificado SSL Básico',
            'description' => 'SSL DV marca blanca. Validación de dominio, cifrado 256-bit.',
            'yearly_price' => 10.00,
        ],
        'SSL_COMODO' => [
            'name' => 'Certificado SSL Comodo',
            'description' => 'SSL DV Comodo/Sectigo. Validación de dominio, sello de confianza.',
            'yearly_price' => 15.00,
        ],
        'SSL_WILDCARD' => [
            'name' => 'Certificado SSL Wildcard',
            'description' => 'SSL Wildcard marca blanca. Cubre *.dominio.com ilimitado.',
            'yearly_price' => 60.00,
        ],
        'SSL_COMODO_WILDCARD' => [
            'name' => 'Certificado SSL Comodo Wildcard',
            'description' => 'SSL Wildcard Comodo/Sectigo. Cubre *.dominio.com, sello confianza.',
            'yearly_price' => 120.00,
        ],
    ];

    private ?TaxGroup $taxGroupGeneral = null;

    private ?UnitMeasure $unitMeasureUnit = null;

    private ?UnitMeasure $unitMeasureMonth = null;

    public function run(): void
    {
        $this->command->info('Creating Spanish hosting products...');

        // Get required references
        $this->loadReferences();

        if (! $this->taxGroupGeneral) {
            $this->command->error('Tax group "IVA General" not found. Run TaxGroupsSeeder first.');

            return;
        }

        // Create products
        $this->createHostingPlans();
        $this->createVpsPlans();
        $this->createDomains();
        $this->createSslCertificates();

        $this->command->info('✅ Spanish hosting products created successfully!');
        $this->command->newLine();
        $this->command->line('  Hosting: '.count(self::HOSTING_PLANS).' plans × 3 frequencies');
        $this->command->line('  VPS: '.count(self::VPS_PLANS).' plans × 3 frequencies');
        $this->command->line('  Domains: '.count(self::DOMAINS).' TLDs (yearly)');
        $this->command->line('  SSL: '.count(self::SSL_CERTIFICATES).' certificates (yearly)');
    }

    /**
     * Load required model references.
     */
    private function loadReferences(): void
    {
        $this->taxGroupGeneral = TaxGroup::where('name', 'IVA General')->first();
        $this->unitMeasureUnit = UnitMeasure::where('code', 'unit')->first();
        $this->unitMeasureMonth = UnitMeasure::where('code', 'month')->first();
    }

    /**
     * Create hosting plans with pricing.
     */
    private function createHostingPlans(): void
    {
        foreach (self::HOSTING_PLANS as $code => $config) {
            $article = $this->createArticle(
                code: "HOSTING_{$code}",
                name: $config['name'],
                description: $config['description'],
                category: 'hosting',
                itemType: ItemType::SERVICE,
                unitMeasure: $this->unitMeasureMonth,
                metadata: [
                    'service' => [
                        'requires_instance' => true,
                        'instance_validation' => ['type' => 'domain'],
                        'instance_label' => 'Dominio asociado',
                    ],
                    'features' => $this->getHostingFeatures($code),
                ]
            );

            $this->createRecurringPrices($article, $config['monthly_price']);
        }

        $this->command->info('  ✓ Hosting plans created');
    }

    /**
     * Create VPS plans with pricing.
     */
    private function createVpsPlans(): void
    {
        foreach (self::VPS_PLANS as $code => $config) {
            $article = $this->createArticle(
                code: "VPS_{$code}",
                name: $config['name'],
                description: $config['description'],
                category: 'vps',
                itemType: ItemType::SERVICE,
                unitMeasure: $this->unitMeasureMonth,
                metadata: [
                    'service' => [
                        'requires_instance' => true,
                        'instance_validation' => ['type' => 'any'],
                        'instance_label' => 'Hostname / IP',
                    ],
                    'specs' => $this->getVpsSpecs($code),
                ]
            );

            $this->createRecurringPrices($article, $config['monthly_price']);
        }

        $this->command->info('  ✓ VPS plans created');
    }

    /**
     * Create domain products.
     */
    private function createDomains(): void
    {
        foreach (self::DOMAINS as $tld => $config) {
            $code = 'DOMAIN_'.strtoupper(str_replace('.', '', $tld));

            $article = $this->createArticle(
                code: $code,
                name: $config['name'],
                description: $config['description'],
                category: 'domains',
                itemType: ItemType::SERVICE,
                unitMeasure: $this->unitMeasureUnit,
                metadata: [
                    'service' => [
                        'requires_instance' => true,
                        'instance_validation' => ['type' => 'domain'],
                        'instance_label' => 'Nombre de dominio',
                    ],
                    'tld' => $tld,
                    'registration_period' => '1 year',
                ]
            );

            // Domains only have yearly pricing
            $this->createPrice(
                $article,
                BillingFrequency::YEARLY,
                $this->eurosToCents($config['yearly_price'])
            );
        }

        $this->command->info('  ✓ Domain products created');
    }

    /**
     * Create SSL certificate products.
     */
    private function createSslCertificates(): void
    {
        foreach (self::SSL_CERTIFICATES as $code => $config) {
            $article = $this->createArticle(
                code: $code,
                name: $config['name'],
                description: $config['description'],
                category: 'ssl',
                itemType: ItemType::SERVICE,
                unitMeasure: $this->unitMeasureUnit,
                metadata: [
                    'service' => [
                        'requires_instance' => true,
                        'instance_validation' => ['type' => 'domain'],
                        'instance_label' => 'Dominio a certificar',
                    ],
                    'ssl_type' => str_contains($code, 'WILDCARD') ? 'wildcard' : 'single',
                    'validation' => 'DV',
                    'validity_period' => '1 year',
                ]
            );

            // SSL certificates only have yearly pricing
            $this->createPrice(
                $article,
                BillingFrequency::YEARLY,
                $this->eurosToCents($config['yearly_price'])
            );
        }

        $this->command->info('  ✓ SSL certificates created');
    }

    /**
     * Create an article.
     */
    private function createArticle(
        string $code,
        string $name,
        string $description,
        string $category,
        ItemType $itemType,
        ?UnitMeasure $unitMeasure,
        array $metadata = []
    ): Article {
        return Article::updateOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'description' => $description,
                'item_type' => $itemType,
                'category' => $category,
                'tax_group_id' => $this->taxGroupGeneral?->id,
                'unit_measure_id' => $unitMeasure?->id,
                'is_active' => true,
                'metadata' => $metadata,
            ]
        );
    }

    /**
     * Create recurring prices (monthly, quarterly, yearly).
     *
     * Pricing strategy:
     * - Monthly: base price
     * - Quarterly: 3x monthly (no discount)
     * - Yearly: 11x monthly (1 month free)
     */
    private function createRecurringPrices(Article $article, float $monthlyPriceEuros): void
    {
        $monthlyPrice = $this->eurosToCents($monthlyPriceEuros);

        // Monthly
        $this->createPrice($article, BillingFrequency::MONTHLY, $monthlyPrice);

        // Quarterly (3 months, no discount)
        $this->createPrice($article, BillingFrequency::QUARTERLY, $monthlyPrice * 3);

        // Yearly (11 months = 1 month free)
        $this->createPrice($article, BillingFrequency::YEARLY, $monthlyPrice * 11);
    }

    /**
     * Create a single price for an article.
     */
    private function createPrice(Article $article, BillingFrequency $frequency, int $priceInCents): void
    {
        ArticlePrice::updateOrCreate(
            [
                'article_id' => $article->id,
                'billing_frequency' => $frequency,
            ],
            [
                'price' => $priceInCents,
                'billing_days_in_advance' => $this->getBillingDaysInAdvance($frequency),
                'valid_from' => now()->startOfYear(),
                'valid_to' => null,
                'is_active' => true,
            ]
        );
    }

    /**
     * Convert euros to cents (Base100Int).
     */
    private function eurosToCents(float $euros): int
    {
        return (int) round($euros * 100);
    }

    /**
     * Get billing days in advance based on frequency.
     */
    private function getBillingDaysInAdvance(BillingFrequency $frequency): int
    {
        return match ($frequency) {
            BillingFrequency::MONTHLY => 7,
            BillingFrequency::QUARTERLY => 14,
            BillingFrequency::YEARLY => 30,
            default => 7,
        };
    }

    /**
     * Get hosting features by plan code.
     */
    private function getHostingFeatures(string $code): array
    {
        return match ($code) {
            'ESTANDAR' => [
                'storage' => '10GB SSD',
                'domains' => 1,
                'email' => 'unlimited',
                'ssl' => false,
                'cdn' => false,
            ],
            'PRO' => [
                'storage' => '50GB SSD',
                'domains' => 5,
                'email' => 'unlimited',
                'ssl' => true,
                'cdn' => false,
            ],
            'EMPRESA' => [
                'storage' => '100GB SSD',
                'domains' => 'unlimited',
                'email' => 'unlimited',
                'ssl' => true,
                'cdn' => true,
            ],
            default => [],
        };
    }

    /**
     * Get VPS specs by plan code.
     */
    private function getVpsSpecs(string $code): array
    {
        return match ($code) {
            'ALFA' => [
                'vcpu' => 2,
                'ram' => '4GB',
                'storage' => '80GB SSD',
                'transfer' => '2TB',
            ],
            'BETA' => [
                'vcpu' => 4,
                'ram' => '8GB',
                'storage' => '160GB SSD',
                'transfer' => '4TB',
            ],
            'GAMMA' => [
                'vcpu' => 8,
                'ram' => '16GB',
                'storage' => '320GB SSD',
                'transfer' => '8TB',
            ],
            default => [],
        };
    }
}
