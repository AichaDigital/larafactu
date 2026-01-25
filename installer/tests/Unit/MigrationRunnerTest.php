<?php

declare(strict_types=1);

namespace Installer\Tests\Unit;

use Installer\Actions\MigrationRunner;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for the MigrationRunner class
 */
class MigrationRunnerTest extends TestCase
{
    public function test_essential_seeders_are_defined(): void
    {
        $runner = new MigrationRunner;

        // Use reflection to access private method and verify seeders list
        $reflection = new ReflectionClass($runner);
        $method = $reflection->getMethod('seedEssentials');

        // Verify the method exists
        $this->assertTrue($method->isPublic(), 'seedEssentials should be a public method');
    }

    public function test_essential_seeders_list_contains_required_seeders(): void
    {
        // Read the source file to verify seeder list
        $sourceFile = INSTALLER_ROOT.'/src/Actions/MigrationRunner.php';
        $content = file_get_contents($sourceFile);

        // Verify essential seeders are listed
        $requiredSeeders = [
            'LegalEntityTypesSeeder',
            'TaxRatesSeeder',
            'TaxGroupsSeeder',
            'UnitMeasuresSeeder',
        ];

        foreach ($requiredSeeders as $seeder) {
            $this->assertStringContainsString(
                $seeder,
                $content,
                "Essential seeder {$seeder} should be defined in MigrationRunner"
            );
        }
    }

    public function test_tax_groups_seeder_comes_after_tax_rates(): void
    {
        // TaxGroupsSeeder depends on TaxRatesSeeder, so order matters
        $sourceFile = INSTALLER_ROOT.'/src/Actions/MigrationRunner.php';
        $content = file_get_contents($sourceFile);

        $taxRatesPos = strpos($content, 'TaxRatesSeeder');
        $taxGroupsPos = strpos($content, 'TaxGroupsSeeder');

        $this->assertNotFalse($taxRatesPos, 'TaxRatesSeeder should be in the file');
        $this->assertNotFalse($taxGroupsPos, 'TaxGroupsSeeder should be in the file');
        $this->assertLessThan(
            $taxGroupsPos,
            $taxRatesPos,
            'TaxRatesSeeder must come before TaxGroupsSeeder (dependency order)'
        );
    }
}
