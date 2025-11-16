<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MedcheckController extends Controller
{
    /**
     * Show the main schedule/status page.
     */
    public function index(Request $request)
    {
        $medcheck = session('medcheck', [
            'schedule' => [],
            'status' => [],
        ]);

        return view('custom', [
            'schedule' => $medcheck['schedule'],
            'status' => $medcheck['status'],
        ]);
    }

    /**
     * Mark a pill as taken for today.
     */
    public function take(Request $request, $id)
    {
        $medcheck = session('medcheck', [
            'schedule' => [],
            'status' => [],
        ]);
        $medcheck['status'][$id] = true;
        session(['medcheck' => $medcheck]);
        return redirect()->route('schedule');
    }

    /**
     * Reset daily status.
     */
    public function reset(Request $request)
    {
        $medcheck = session('medcheck', [
            'schedule' => [],
            'status' => [],
        ]);
        $medcheck['status'] = [];
        session(['medcheck' => $medcheck]);
        return redirect()->route('schedule');
    }

    /**
     * Show the setup page.
     */
    public function setup(Request $request)
    {
        $medcheck = session('medcheck', [
            'schedule' => [],
            'status' => [],
        ]);
        return view('setup', [
            'schedule' => $medcheck['schedule'],
        ]);
    }

    /**
     * Add a new pill to the schedule.
     */
    public function addPill(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'time' => 'required|in:morning,afternoon,evening',
        ]);

        // Sanitize name
        $name = strip_tags(trim($validated['name']));
        $qty = (int) $validated['qty'];
        $time = $validated['time'];

        $medcheck = session('medcheck', [
            'schedule' => [],
            'status' => [],
        ]);
        $id = count($medcheck['schedule']) ? max(array_column($medcheck['schedule'], 'id')) + 1 : 1;
        $medcheck['schedule'][] = [
            'id' => $id,
            'name' => $name,
            'qty' => $qty,
            'time' => $time,
        ];
        // Initialize status for new pill
        $medcheck['status'][$id] = false;
        session(['medcheck' => $medcheck]);
        return redirect()->route('setup');
    }

    /**
     * Edit an existing pill.
     */
    public function editPill(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'time' => 'required|in:morning,afternoon,evening',
        ]);

        // Sanitize name
        $name = strip_tags(trim($validated['name']));
        $qty = (int) $validated['qty'];
        $time = $validated['time'];

        $medcheck = session('medcheck', [
            'schedule' => [],
            'status' => [],
        ]);
        foreach ($medcheck['schedule'] as &$pill) {
            if ($pill['id'] == $id) {
                $pill['name'] = $name;
                $pill['qty'] = $qty;
                $pill['time'] = $time;
                break;
            }
        }
        session(['medcheck' => $medcheck]);
        return redirect()->route('setup');
    }

    /**
     * Delete a pill from the schedule.
     */
    public function deletePill(Request $request, $id)
    {
        $medcheck = session('medcheck', [
            'schedule' => [],
            'status' => [],
        ]);
        $medcheck['schedule'] = array_filter($medcheck['schedule'], function ($pill) use ($id) {
            return $pill['id'] != $id;
        });
        // Clean up orphaned status entry
        unset($medcheck['status'][$id]);
        session(['medcheck' => $medcheck]);
        return redirect()->route('setup');
    }

    /**
     * Show the export/import page.
     */
    public function exportPage(Request $request)
    {
        return view('export');
    }

    /**
     * Export session data as JSON.
     */
    public function exportData(Request $request)
    {
        $medcheck = session('medcheck', [
            'schedule' => [],
            'status' => [],
        ]);
        $json = json_encode($medcheck, JSON_PRETTY_PRINT);

        // Generate timestamp for filename (YYYY-MM-DD_HHMMSS format for good sorting)
        $timestamp = now()->format('Y-m-d_His');
        $filename = "medcheck-export-{$timestamp}.json";

        return response($json)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    /**
     * Import session data from JSON.
     */
    public function importData(Request $request)
    {
        $request->validate([
            'import_json' => 'required|string',
        ]);
        $data = json_decode($request->input('import_json'), true);
        if (!is_array($data) || !isset($data['schedule']) || !is_array($data['schedule'])) {
            return back()->withErrors(['import_json' => 'Invalid data format.']);
        }
        // Optionally validate pills
        foreach ($data['schedule'] as $pill) {
            if (
                !isset($pill['name'], $pill['qty'], $pill['time']) ||
                !is_string($pill['name']) || strlen($pill['name']) > 255 ||
                !is_numeric($pill['qty']) || $pill['qty'] < 1 ||
                !in_array($pill['time'], ['morning', 'afternoon', 'evening'])
            ) {
                return back()->withErrors(['import_json' => 'Invalid pill data.']);
            }
        }
        // Status is optional
        $status = isset($data['status']) && is_array($data['status']) ? $data['status'] : [];
        session(['medcheck' => [
            'schedule' => $data['schedule'],
            'status' => $status,
        ]]);
        return redirect()->route('setup')->with('success', 'Data imported successfully.');
    }
}
