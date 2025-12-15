<?php

declare(strict_types=1);

namespace Database\Seeders;

use AichaDigital\Larabill\Models\UserTaxProfile;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * UsersSeeder - Creates 100 users with fiscal profiles.
 *
 * Distribution:
 * - 90 Spanish users (ES): individuals, companies, associations
 * - 10 EU users: for intra-community VAT scenarios
 *
 * Simulates year 2025 with valid_from dates throughout the year.
 *
 * @see ADR-003 for User/Customer unification architecture
 */
class UsersSeeder extends Seeder
{
    /**
     * Spanish legal entity types distribution.
     * Code => weight (higher = more likely)
     */
    private const ES_ENTITY_DISTRIBUTION = [
        // Personas físicas (~30%)
        'INDIVIDUAL' => 15,
        'SELF_EMPLOYED' => 15,

        // Empresas más comunes (~50%)
        'LIMITED_COMPANY' => 30,
        'PUBLIC_LIMITED_COMPANY' => 10,
        'COOPERATIVE' => 5,
        'COMMUNITY_OF_GOODS' => 5,

        // Asociaciones y fundaciones (~20%)
        'ASSOCIATION' => 10,
        'FOUNDATION' => 5,
        'CIVIL_PARTNERSHIP' => 5,
    ];

    /**
     * EU countries for non-Spanish users.
     */
    private const EU_COUNTRIES = [
        'PT' => ['locale' => 'pt_PT', 'name' => 'Portugal'],
        'FR' => ['locale' => 'fr_FR', 'name' => 'France'],
        'DE' => ['locale' => 'de_DE', 'name' => 'Germany'],
        'IT' => ['locale' => 'it_IT', 'name' => 'Italy'],
        'NL' => ['locale' => 'nl_NL', 'name' => 'Netherlands'],
        'BE' => ['locale' => 'nl_BE', 'name' => 'Belgium'],
        'IE' => ['locale' => 'en_IE', 'name' => 'Ireland'],
        'AT' => ['locale' => 'de_AT', 'name' => 'Austria'],
        'PL' => ['locale' => 'pl_PL', 'name' => 'Poland'],
        'SE' => ['locale' => 'sv_SE', 'name' => 'Sweden'],
    ];

    /**
     * Spanish cities with provinces.
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
        ['city' => 'Palma', 'state' => 'Baleares', 'zip_prefix' => '07'],
        ['city' => 'Las Palmas', 'state' => 'Las Palmas', 'zip_prefix' => '35'],
        ['city' => 'A Coruña', 'state' => 'A Coruña', 'zip_prefix' => '15'],
        ['city' => 'Valladolid', 'state' => 'Valladolid', 'zip_prefix' => '47'],
        ['city' => 'Granada', 'state' => 'Granada', 'zip_prefix' => '18'],
        ['city' => 'Oviedo', 'state' => 'Asturias', 'zip_prefix' => '33'],
    ];

    private \Faker\Generator $fakerEs;

    private array $usedEmails = [];

    private array $usedTaxIds = [];

    public function run(): void
    {
        $this->fakerEs = Faker::create('es_ES');

        $this->command->info('Creating 100 users with fiscal profiles...');

        // Create 90 Spanish users
        $this->createSpanishUsers(90);

        // Create 10 EU users
        $this->createEuUsers(10);

        $this->command->info('✅ 100 users created with UserTaxProfile records');
    }

    /**
     * Create Spanish users with fiscal profiles.
     */
    private function createSpanishUsers(int $count): void
    {
        $entityTypes = $this->buildWeightedEntityList();

        for ($i = 0; $i < $count; $i++) {
            $entityCode = $entityTypes[array_rand($entityTypes)];
            $isCompany = $this->isCompanyType($entityCode);

            $user = $this->createUser($entityCode, 'ES', $isCompany);
            $this->createTaxProfile($user, $entityCode, 'ES', $isCompany);

            if (($i + 1) % 30 === 0) {
                $progress = $i + 1;
                $this->command->info("  Created {$progress} Spanish users...");
            }
        }
    }

    /**
     * Create EU users for intra-community scenarios.
     */
    private function createEuUsers(int $count): void
    {
        $countries = array_keys(self::EU_COUNTRIES);

        for ($i = 0; $i < $count; $i++) {
            $countryCode = $countries[$i % count($countries)];
            $countryInfo = self::EU_COUNTRIES[$countryCode];

            $faker = Faker::create($countryInfo['locale']);

            // EU users are typically companies for B2B scenarios
            $isCompany = $this->fakerEs->boolean(80); // 80% companies

            $user = $this->createEuUser($faker, $countryCode, $isCompany);
            $this->createEuTaxProfile($user, $faker, $countryCode, $isCompany);
        }

        $this->command->info("  Created {$count} EU users");
    }

    /**
     * Create a Spanish user.
     */
    private function createUser(string $entityCode, string $countryCode, bool $isCompany): User
    {
        if ($isCompany) {
            $name = $this->generateCompanyName($entityCode);
            $emailPrefix = Str::slug(Str::limit($name, 20, ''));
        } else {
            $name = $this->fakerEs->name();
            $emailPrefix = Str::slug($name);
        }

        $email = $this->generateUniqueEmail($emailPrefix);

        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'email_verified_at' => $this->generate2025Date(),
        ]);
    }

    /**
     * Create an EU user.
     */
    private function createEuUser(\Faker\Generator $faker, string $countryCode, bool $isCompany): User
    {
        if ($isCompany) {
            $name = $faker->company();
            $emailPrefix = Str::slug(Str::limit($name, 20, ''));
        } else {
            $name = $faker->name();
            $emailPrefix = Str::slug($name);
        }

        $email = $this->generateUniqueEmail($emailPrefix, strtolower($countryCode));

        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'email_verified_at' => $this->generate2025Date(),
        ]);
    }

    /**
     * Create tax profile for Spanish user.
     */
    private function createTaxProfile(User $user, string $entityCode, string $countryCode, bool $isCompany): void
    {
        $location = $this->fakerEs->randomElement(self::ES_CITIES);

        $taxId = $isCompany
            ? $this->generateSpanishCIF($entityCode)
            : $this->generateSpanishNIF();

        UserTaxProfile::create([
            'user_id' => $user->id,
            'fiscal_name' => $user->name,
            'tax_id' => $taxId,
            'legal_entity_type_code' => $entityCode,
            'address' => $this->fakerEs->streetAddress(),
            'city' => $location['city'],
            'state' => $location['state'],
            'zip_code' => $location['zip_prefix'].str_pad((string) $this->fakerEs->numberBetween(0, 999), 3, '0', STR_PAD_LEFT),
            'country_code' => $countryCode,
            'is_company' => $isCompany,
            'is_eu_vat_registered' => false,
            'is_exempt_vat' => $this->isVatExempt($entityCode),
            'valid_from' => $this->generate2025ValidFrom(),
            'valid_until' => null,
            'is_active' => true,
            'notes' => "Seeder 2025 - {$entityCode}",
        ]);
    }

    /**
     * Create tax profile for EU user.
     */
    private function createEuTaxProfile(User $user, \Faker\Generator $faker, string $countryCode, bool $isCompany): void
    {
        $vatNumber = $this->generateEuVatNumber($countryCode);

        UserTaxProfile::create([
            'user_id' => $user->id,
            'fiscal_name' => $user->name,
            'tax_id' => $vatNumber,
            'legal_entity_type_code' => $isCompany ? 'LIMITED_COMPANY' : 'INDIVIDUAL',
            'address' => $faker->streetAddress(),
            'city' => $faker->city(),
            'state' => '',
            'zip_code' => $faker->postcode(),
            'country_code' => $countryCode,
            'is_company' => $isCompany,
            'is_eu_vat_registered' => $isCompany, // B2B intra-community
            'is_exempt_vat' => false,
            'valid_from' => $this->generate2025ValidFrom(),
            'valid_until' => null,
            'is_active' => true,
            'notes' => "Seeder 2025 - EU {$countryCode}",
        ]);
    }

    /**
     * Generate a valid Spanish NIF (for individuals).
     * Format: 8 digits + control letter
     */
    private function generateSpanishNIF(): string
    {
        $letters = 'TRWAGMYFPDXBNJZSQVHLCKE';

        do {
            $number = $this->fakerEs->numberBetween(10000000, 99999999);
            $letter = $letters[$number % 23];
            $nif = $number.$letter;
        } while (in_array($nif, $this->usedTaxIds, true));

        $this->usedTaxIds[] = $nif;

        return $nif;
    }

    /**
     * Generate a valid Spanish CIF (for companies/associations).
     * Format: Letter + 7 digits + control (digit or letter)
     */
    private function generateSpanishCIF(string $entityCode): string
    {
        // First letter depends on entity type
        $prefixes = match ($entityCode) {
            'LIMITED_COMPANY', 'NEW_ENTERPRISE_LIMITED', 'WORKER_OWNED_LIMITED' => ['B'],
            'PUBLIC_LIMITED_COMPANY', 'WORKER_OWNED_PUBLIC' => ['A'],
            'COOPERATIVE' => ['F'],
            'ASSOCIATION' => ['G'],
            'FOUNDATION' => ['G'],
            'COMMUNITY_OF_GOODS', 'CIVIL_PARTNERSHIP' => ['E'],
            'ECONOMIC_INTEREST_GROUPING' => ['V'],
            'JOINT_VENTURE' => ['U'],
            default => ['B'],
        };

        $prefix = $this->fakerEs->randomElement($prefixes);

        do {
            $digits = str_pad((string) $this->fakerEs->numberBetween(1000000, 9999999), 7, '0', STR_PAD_LEFT);

            // Calculate control character (simplified)
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
        $digits = substr($cifWithoutControl, 1); // Remove letter prefix
        $sumEven = 0;
        $sumOdd = 0;

        for ($i = 0; $i < 7; $i++) {
            $digit = (int) $digits[$i];
            if ($i % 2 === 0) {
                // Odd positions (1, 3, 5, 7): multiply by 2
                $doubled = $digit * 2;
                $sumOdd += (int) floor($doubled / 10) + ($doubled % 10);
            } else {
                // Even positions (2, 4, 6): add directly
                $sumEven += $digit;
            }
        }

        $total = $sumEven + $sumOdd;
        $control = (10 - ($total % 10)) % 10;

        // Some CIF types use letter, others use digit
        $letterPrefixes = ['K', 'P', 'Q', 'S', 'N', 'W', 'R'];
        $prefix = $cifWithoutControl[0];

        if (in_array($prefix, $letterPrefixes, true)) {
            $controlLetters = 'JABCDEFGHI';

            return $controlLetters[$control];
        }

        return (string) $control;
    }

    /**
     * Generate EU VAT number.
     */
    private function generateEuVatNumber(string $countryCode): string
    {
        // Simplified format: CC + 8-12 digits
        $length = match ($countryCode) {
            'AT' => 9,  // ATU + 8 digits
            'BE' => 10, // BE + 10 digits
            'DE' => 9,  // DE + 9 digits
            'FR' => 11, // FR + 2 chars + 9 digits
            'IT' => 11, // IT + 11 digits
            'NL' => 12, // NL + 9 digits + B + 2 digits
            'PT' => 9,  // PT + 9 digits
            default => 9,
        };

        $digits = '';
        for ($i = 0; $i < $length; $i++) {
            $digits .= $this->fakerEs->randomDigit();
        }

        return $countryCode.$digits;
    }

    /**
     * Generate company name based on entity type.
     */
    private function generateCompanyName(string $entityCode): string
    {
        $baseName = $this->fakerEs->company();

        $suffix = match ($entityCode) {
            'LIMITED_COMPANY' => ' S.L.',
            'PUBLIC_LIMITED_COMPANY' => ' S.A.',
            'NEW_ENTERPRISE_LIMITED' => ' S.L.N.E.',
            'WORKER_OWNED_LIMITED' => ' S.L.L.',
            'WORKER_OWNED_PUBLIC' => ' S.A.L.',
            'COOPERATIVE' => ' S.Coop.',
            'ASSOCIATION' => '',
            'FOUNDATION' => '',
            'COMMUNITY_OF_GOODS' => ' C.B.',
            'CIVIL_PARTNERSHIP' => ' S.C.',
            default => '',
        };

        // For associations/foundations, use different naming
        if ($entityCode === 'ASSOCIATION') {
            $topics = ['Cultural', 'Deportiva', 'Vecinal', 'Profesional', 'Juvenil', 'de Comerciantes'];

            return 'Asociación '.$this->fakerEs->randomElement($topics).' '.$this->fakerEs->city();
        }

        if ($entityCode === 'FOUNDATION') {
            return 'Fundación '.$this->fakerEs->lastName();
        }

        return $baseName.$suffix;
    }

    /**
     * Build weighted list of entity types for random selection.
     */
    private function buildWeightedEntityList(): array
    {
        $list = [];
        foreach (self::ES_ENTITY_DISTRIBUTION as $code => $weight) {
            for ($i = 0; $i < $weight; $i++) {
                $list[] = $code;
            }
        }

        return $list;
    }

    /**
     * Check if entity type is a company (vs individual).
     */
    private function isCompanyType(string $entityCode): bool
    {
        return ! in_array($entityCode, ['INDIVIDUAL', 'SELF_EMPLOYED'], true);
    }

    /**
     * Check if entity type is VAT exempt.
     */
    private function isVatExempt(string $entityCode): bool
    {
        // Associations and foundations may be VAT exempt
        return in_array($entityCode, ['ASSOCIATION', 'FOUNDATION'], true)
            && $this->fakerEs->boolean(50);
    }

    /**
     * Generate unique email address.
     */
    private function generateUniqueEmail(string $prefix, string $domain = 'example'): string
    {
        $baseEmail = strtolower(preg_replace('/[^a-z0-9]/', '', $prefix));
        $baseEmail = Str::limit($baseEmail, 30, '');

        $email = "{$baseEmail}@{$domain}.com";
        $counter = 1;

        while (in_array($email, $this->usedEmails, true)) {
            $email = "{$baseEmail}{$counter}@{$domain}.com";
            $counter++;
        }

        $this->usedEmails[] = $email;

        return $email;
    }

    /**
     * Generate a date in 2025.
     */
    private function generate2025Date(): \Carbon\Carbon
    {
        return \Carbon\Carbon::create(2025, rand(1, 12), rand(1, 28));
    }

    /**
     * Generate valid_from date in 2025 (mostly early in the year).
     */
    private function generate2025ValidFrom(): \Carbon\Carbon
    {
        // 70% in Q1, 30% rest of year
        if ($this->fakerEs->boolean(70)) {
            return \Carbon\Carbon::create(2025, rand(1, 3), rand(1, 28));
        }

        return \Carbon\Carbon::create(2025, rand(1, 12), rand(1, 28));
    }
}
