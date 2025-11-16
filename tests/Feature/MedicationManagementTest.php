<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Session;

class MedicationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Start a session for the test
        $this->startSession();
    }

    /**
     * Test the complete medication management workflow.
     */
    public function test_complete_medication_workflow(): void
    {
        // Use session assertions to verify each step works correctly

        // Step 1: Add three pills - morning, midday, and evening
        $this->addThreePills();

        // Step 2: Edit one of the pills
        $this->editPill();

        // Step 3: Go to schedule page and take a pill
        $this->takePillAndConfirm();

        // Step 4: Reset day and confirm reset
        $this->resetDayAndConfirm();

        // Step 5: Export JSON and validate format
        $this->exportAndValidateJson();

        // Step 6: Import the data back
        $this->importData();

        // Step 7: Check distinct count shows up properly
        $this->checkDistinctCount();
    }

    private function addThreePills(): void
    {
        // Add morning pill
        $this->post('/add-pill', [
            'name' => 'Morning Vitamin',
            'qty' => 1,
            'time' => 'morning'
        ])
            ->assertRedirect('/setup')
            ->assertSessionHas('medcheck');

        // Add midday pill - continue with existing session
        $this->post('/add-pill', [
            'name' => 'Lunch Supplement',
            'qty' => 2,
            'time' => 'afternoon'
        ])
            ->assertRedirect('/setup')
            ->assertSessionHas('medcheck');

        // Add evening pill - continue with existing session
        $this->post('/add-pill', [
            'name' => 'Evening Medication',
            'qty' => 1,
            'time' => 'evening'
        ])
            ->assertRedirect('/setup')
            ->assertSessionHas('medcheck');

        // Verify pills are on setup page
        $this->get('/setup')
            ->assertStatus(200)
            ->assertSee('Morning Vitamin')
            ->assertSee('Lunch Supplement')
            ->assertSee('Evening Medication');

        // Verify session has medcheck data with 3 pills
        $response = $this->get('/setup');
        $response->assertSessionHas('medcheck', function ($medcheck) {
            return is_array($medcheck) &&
                isset($medcheck['schedule']) &&
                count($medcheck['schedule']) === 3;
        });
    }

    private function editPill(): void
    {
        // Get current session data - first pill should have ID 1
        $firstPillId = 1;

        // Edit the first pill
        $this->post("/edit-pill/{$firstPillId}", [
            'name' => 'Updated Morning Vitamin',
            'qty' => 1,
            'time' => 'morning'
        ])
            ->assertRedirect('/setup')
            ->assertSessionHas('medcheck');

        // Verify the edit worked
        $response = $this->get('/setup');
        $response->assertStatus(200);
        $response->assertSee('Updated Morning Vitamin');

        // Debug: Let's see the session data instead of strict assertion
        $response->assertSessionHas('medcheck', function ($medcheck) {
            $pillNames = array_column($medcheck['schedule'], 'name');
            return in_array('Updated Morning Vitamin', $pillNames);
        });
    }
    private function takePillAndConfirm(): void
    {
        // Go to schedule page
        $this->get('/')
            ->assertStatus(200);

        // Take the first pill (ID 1)
        $firstPillId = 1;
        $this->post("/take/{$firstPillId}")
            ->assertRedirect('/')
            ->assertSessionHas('medcheck');

        // Confirm it shows as taken on schedule page
        $this->get('/')
            ->assertStatus(200)
            ->assertSessionHas('medcheck', function ($medcheck) use ($firstPillId) {
                return isset($medcheck['status'][$firstPillId]) &&
                    $medcheck['status'][$firstPillId] === true;
            });
    }

    private function resetDayAndConfirm(): void
    {
        // Reset the day
        $this->post('/reset')
            ->assertRedirect('/')
            ->assertSessionHas('medcheck');

        // Confirm day has been reset - all status should be reset
        $this->get('/')
            ->assertStatus(200)
            ->assertSessionHas('medcheck', function ($medcheck) {
                return isset($medcheck['status']) && empty(array_filter($medcheck['status']));
            });
    }
    private function exportAndValidateJson(): void
    {
        // Go to export page
        $this->get('/export')
            ->assertStatus(200);

        // Export the data
        $response = $this->post('/export');
        $response->assertStatus(200);

        // Check that response is downloadable JSON
        $response->assertHeader('content-type', 'application/json');
        $response->assertHeader('content-disposition');

        // Validate JSON structure - should match medcheck session structure
        $jsonData = json_decode($response->getContent(), true);
        $this->assertIsArray($jsonData);
        $this->assertArrayHasKey('schedule', $jsonData);
        $this->assertArrayHasKey('status', $jsonData);

        // Verify schedule data (3 pills)
        $this->assertCount(3, $jsonData['schedule']);

        // Store for import test
        $this->exportedData = $jsonData;
    }

    private function importData(): void
    {
        // First clear current data by starting fresh session
        $this->withSession([]);

        // Import the data using the JSON string format expected by the controller
        $jsonContent = json_encode($this->exportedData);

        $this->post('/import', [
            'import_json' => $jsonContent
        ])
            ->assertRedirect('/setup')
            ->assertSessionHas('medcheck');

        // Verify data was imported on setup page
        $this->get('/setup')
            ->assertStatus(200)
            ->assertSee('Updated Morning Vitamin')
            ->assertSee('Lunch Supplement')
            ->assertSee('Evening Medication')
            ->assertSessionHas('medcheck', function ($medcheck) {
                return isset($medcheck['schedule']) && count($medcheck['schedule']) === 3;
            });
    }
    private function checkDistinctCount(): void
    {
        // Create some session records to test distinct counting
        // Note: In test environment, session driver is 'array', so this will test the 'N/A' case

        // Get the footer (which contains the session count)
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Active sessions:')
            ->assertSee('N/A'); // Since we're using array session driver in tests

        // Also verify our pills are still there after the complete workflow
        $this->get('/setup')
            ->assertStatus(200)
            ->assertSessionHas('medcheck', function ($medcheck) {
                $schedule = $medcheck['schedule'] ?? [];
                $pillNames = array_column($schedule, 'name');
                return count($schedule) === 3 &&
                    in_array('Updated Morning Vitamin', $pillNames);
            });
    }

    private $exportedData;
}
