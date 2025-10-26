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
        session(['medcheck' => $medcheck]);
        return redirect()->route('setup');
    }
}
