<?php

declare(strict_types=1);

namespace Installer\Tests\Unit;

use Installer\Session\InstallState;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the InstallState class
 */
class InstallStateTest extends TestCase
{
    private string $stateFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateFile = INSTALLER_ROOT.'/storage/install_state.json';

        // Clean up before each test
        if (file_exists($this->stateFile)) {
            unlink($this->stateFile);
        }
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        if (file_exists($this->stateFile)) {
            unlink($this->stateFile);
        }
        parent::tearDown();
    }

    public function test_can_set_and_get_values(): void
    {
        $state = new InstallState;

        $state->set('test_key', 'test_value');

        $this->assertEquals('test_value', $state->get('test_key'));
    }

    public function test_returns_default_for_missing_key(): void
    {
        $state = new InstallState;

        $result = $state->get('nonexistent', 'default_value');

        $this->assertEquals('default_value', $result);
    }

    public function test_can_use_dot_notation(): void
    {
        $state = new InstallState;

        $state->set('database.host', 'localhost');
        $state->set('database.port', 3306);

        $this->assertEquals('localhost', $state->get('database.host'));
        $this->assertEquals(3306, $state->get('database.port'));
    }

    public function test_current_step_defaults_to_one(): void
    {
        $state = new InstallState;

        $this->assertEquals(1, $state->getCurrentStep());
    }

    public function test_can_set_current_step(): void
    {
        $state = new InstallState;

        $state->setCurrentStep(5);

        $this->assertEquals(5, $state->getCurrentStep());
    }

    public function test_step_cannot_be_less_than_one(): void
    {
        $state = new InstallState;

        $state->setCurrentStep(0);

        $this->assertEquals(1, $state->getCurrentStep());
    }

    public function test_can_mark_step_completed(): void
    {
        $state = new InstallState;

        $state->markStepCompleted('welcome');
        $state->markStepCompleted('requirements');

        $this->assertTrue($state->isStepCompleted('welcome'));
        $this->assertTrue($state->isStepCompleted('requirements'));
        $this->assertFalse($state->isStepCompleted('database'));
    }

    public function test_completed_steps_are_unique(): void
    {
        $state = new InstallState;

        $state->markStepCompleted('welcome');
        $state->markStepCompleted('welcome'); // Duplicate

        $completed = $state->getCompletedSteps();

        $this->assertCount(1, $completed);
    }

    public function test_state_persists_between_instances(): void
    {
        $state1 = new InstallState;
        $state1->set('persistent_key', 'persistent_value');

        // Create new instance (simulating new request)
        $state2 = new InstallState;

        $this->assertEquals('persistent_value', $state2->get('persistent_key'));
    }

    public function test_clear_removes_all_state(): void
    {
        $state = new InstallState;
        $state->set('key1', 'value1');
        $state->set('key2', 'value2');

        $state->clear();

        $this->assertNull($state->get('key1'));
        $this->assertNull($state->get('key2'));
        $this->assertEquals(1, $state->getCurrentStep());
    }

    public function test_has_returns_true_for_existing_key(): void
    {
        $state = new InstallState;
        $state->set('exists', 'value');

        $this->assertTrue($state->has('exists'));
        $this->assertFalse($state->has('not_exists'));
    }
}
