<?php

// simple app to keep a check on your pill taking  schedule 

// i want to be able to define the schedule :
// - pill name qty morning 
// - pill name qty afternoon
// - pill name qty evening 

// i want to store the structure in a session 
// i want to store the progress status in a session
// add a reset button to start a new day 

// so the session should last a year, and get refreshed 
// every time the app cycles, 
// it should start with ( initially empty ) schedule 
// and add another menu link to setup the daily pills 
// i would like this to be as single page as possible 

// Configure session to persist for 30 days
ini_set('session.cookie_lifetime', 30 * 24 * 60 * 60); // 30 days in seconds
ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60);   // Server-side cleanup

session_start();
if (!isset($_SESSION['medcheck'])) {
    $_SESSION['medcheck'] = [
        'schedule' => [
            [
                'id' => 1,
                'name' => 'Aspirin',
                'qty' => 2,
                'time' => 'morning',
            ],
            [
                'id' => 2,
                'name' => 'Vitamin D',
                'qty' => 1,
                'time' => 'afternoon',
            ],
        ],
        'status' => [],
    ];
}

// Function to ensure status array is synced with schedule
function syncStatusWithSchedule()
{
    // Safety check - ensure schedule exists and is array
    if (!isset($_SESSION['medcheck']['schedule']) || !is_array($_SESSION['medcheck']['schedule'])) {
        $_SESSION['medcheck']['schedule'] = [];
    }

    // Safety check - ensure status exists and is array
    if (!isset($_SESSION['medcheck']['status']) || !is_array($_SESSION['medcheck']['status'])) {
        $_SESSION['medcheck']['status'] = [];
    }

    // If no schedule, clear status and return
    if (empty($_SESSION['medcheck']['schedule'])) {
        $_SESSION['medcheck']['status'] = [];
        return;
    }

    // Create a lookup array of existing status entries
    $existingStatus = [];
    foreach ($_SESSION['medcheck']['status'] as $status) {
        if (isset($status['id'])) {
            $existingStatus[$status['id']] = $status;
        }
    }

    // Rebuild status array to match schedule
    $newStatus = [];
    foreach ($_SESSION['medcheck']['schedule'] as $med) {
        if (isset($med['id'])) {
            // Keep existing status if it exists, otherwise create new
            $newStatus[] = [
                'id' => $med['id'],
                'taken' => isset($existingStatus[$med['id']]) ? $existingStatus[$med['id']]['taken'] : false,
                'taken_at' => isset($existingStatus[$med['id']]['taken_at']) ? $existingStatus[$med['id']]['taken_at'] : null
            ];
        }
    }

    $_SESSION['medcheck']['status'] = $newStatus;
}

// Always sync status with schedule on page load
syncStatusWithSchedule();

// Helper functions for safe operations

function markMedicationTaken($medId)
{
    foreach ($_SESSION['medcheck']['status'] as &$status) {
        if ($status['id'] == $medId) {
            $status['taken'] = true;
            $status['taken_at'] = date('H:i');
            return true;
        }
    }
    return false; // Medication not found
}

function markMedicationNotTaken($medId)
{
    foreach ($_SESSION['medcheck']['status'] as &$status) {
        if ($status['id'] == $medId) {
            $status['taken'] = false;
            $status['taken_at'] = null;
            return true;
        }
    }
    return false; // Medication not found
}

function resetDay()
{
    // Reset all medications to not taken
    foreach ($_SESSION['medcheck']['status'] as &$status) {
        $status['taken'] = false;
        $status['taken_at'] = null;
    }
}

function getMedicationStatus($medId)
{
    foreach ($_SESSION['medcheck']['status'] as $status) {
        if ($status['id'] == $medId) {
            return $status;
        }
    }
    return ['id' => $medId, 'taken' => false, 'taken_at' => null]; // Safe default
}

function getDailyProgress()
{
    $total = count($_SESSION['medcheck']['schedule']);
    $taken = 0;

    foreach ($_SESSION['medcheck']['status'] as $status) {
        if ($status['taken']) {
            $taken++;
        }
    }

    return [
        'total' => $total,
        'taken' => $taken,
        'remaining' => $total - $taken,
        'percentage' => $total > 0 ? round(($taken / $total) * 100) : 0
    ];
}

// and the time designation is just a string for now, the real ordering is the id for now 
/* 1- i think status can be left empty at the start, and the meds are added when taken , at the end of the day, when the rest button is presses, then status can be empties again 
2- i think the daily reset should be manual , as in, a button pressed by the user ..
honestly if you find a populated status easier to hande, i don't mind 
*/

function generateHTML()
{
    $currentPage = $_GET['page'] ?? 'schedule';
    $progress = getDailyProgress();

    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCheck - Medication Tracker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        
        .header {
            background-color: #4a90e2;
            height: 80px;
            color: white;
            display: flex;
            align-items: center;
            padding: 0 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: normal;
        }
        
        .menu {
            background-color: #357abd;
            padding: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .menu-items {
            display: flex;
            list-style: none;
        }
        
        .menu-item {
            border-right: 1px solid rgba(255,255,255,0.2);
        }
        
        .menu-item:last-child {
            border-right: none;
        }
        
        .menu-link {
            display: block;
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .menu-link:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .menu-link.active {
            background-color: rgba(255,255,255,0.2);
        }
        
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .body-section {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .progress-bar {
            background-color: #e9ecef;
            border-radius: 10px;
            height: 20px;
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .progress-fill {
            background-color: #28a745;
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
            width: ' . $progress['percentage'] . '%;
        }
        
        .progress-text {
            text-align: center;
            margin-bottom: 10px;
            color: #666;
            font-size: 14px;
        }
        
        .medication-list {
            display: grid;
            gap: 15px;
        }
        
        .medication-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .medication-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .medication-item.taken {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .medication-info {
            flex-grow: 1;
        }
        
        .medication-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .medication-details {
            font-size: 14px;
            color: #666;
        }
        
        .medication-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .taken-time {
            font-size: 12px;
            color: #28a745;
            font-weight: bold;
        }
        
        .reset-section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
        }
        
        .time-badge {
            background-color: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            text-transform: capitalize;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>💊 MedCheck</h1>
    </div>
    
    <nav class="menu">
        <ul class="menu-items">
            <li class="menu-item">
                <a href="?page=schedule" class="menu-link ' . ($currentPage === 'schedule' ? 'active' : '') . '">Schedule</a>
            </li>
            <li class="menu-item">
                <a href="?page=setup" class="menu-link ' . ($currentPage === 'setup' ? 'active' : '') . '">Setup</a>
            </li>
        </ul>
    </nav>
    
    <div class="container">
        <div class="body-section">
            ' . generateBodyContent($currentPage) . '
        </div>
    </div>
</body>
</html>';
}

function generateBodyContent($page)
{
    switch ($page) {
        case 'setup':
            return generateSetupContent();
        case 'schedule':
        default:
            return generateScheduleContent();
    }
}

function generateScheduleContent()
{
    $progress = getDailyProgress();
    $schedule = $_SESSION['medcheck']['schedule'];

    $content = '<h2>Today\'s Medications</h2>';

    // Progress bar
    $content .= '<div class="progress-text">Progress: ' . $progress['taken'] . ' of ' . $progress['total'] . ' medications taken (' . $progress['percentage'] . '%)</div>';
    $content .= '<div class="progress-bar">
        <div class="progress-fill"></div>
    </div>';

    // Medication list
    if (empty($schedule)) {
        $content .= '<div class="empty-state">
            <h3>No medications scheduled</h3>
            <p>Go to <a href="?page=setup">Setup</a> to add your medications.</p>
        </div>';
    } else {
        $content .= '<div class="medication-list">';

        foreach ($schedule as $med) {
            $status = getMedicationStatus($med['id']);
            $isTaken = $status['taken'];
            $takenAt = $status['taken_at'];

            $content .= '<div class="medication-item ' . ($isTaken ? 'taken' : '') . '">
                <div class="medication-info">
                    <div class="medication-name">' . htmlspecialchars($med['name']) . '</div>
                    <div class="medication-details">
                        <span class="time-badge">' . htmlspecialchars($med['time']) . '</span>
                        Quantity: ' . (int)$med['qty'] . '
                        ' . ($isTaken && $takenAt ? '<span class="taken-time">Taken at ' . htmlspecialchars($takenAt) . '</span>' : '') . '
                    </div>
                </div>
                <div class="medication-actions">';

            if ($isTaken) {
                $content .= '<button class="btn btn-secondary" onclick="markNotTaken(' . $med['id'] . ')">Undo</button>';
            } else {
                $content .= '<button class="btn btn-success" onclick="markTaken(' . $med['id'] . ')">Take</button>';
            }

            $content .= '</div>
            </div>';
        }

        $content .= '</div>';
    }

    // Reset section
    $content .= '<div class="reset-section">
        <button class="btn btn-danger" onclick="resetDay()">Reset Day</button>
        <p style="margin-top: 10px; font-size: 12px; color: #666;">
            This will mark all medications as not taken for a new day.
        </p>
    </div>';

    // JavaScript
    $content .= '<script>
        function markTaken(medId) {
            window.location.href = "?action=mark_taken&id=" + medId;
        }
        
        function markNotTaken(medId) {
            window.location.href = "?action=mark_not_taken&id=" + medId;
        }
        
        function resetDay() {
            if (confirm("Are you sure you want to reset the day? This will mark all medications as not taken.")) {
                window.location.href = "?action=reset_day";
            }
        }
    </script>';

    return $content;
}

function generateSetupContent()
{
    $schedule = $_SESSION['medcheck']['schedule'];
    $isEditing = isset($_GET['edit']) ? (int)$_GET['edit'] : null;

    $content = '<h2>⚙️ Setup Medications</h2>';

    // Add new medication form
    $content .= '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
        <h3>➕ Add New Medication</h3>
        <form method="POST" action="?page=setup&action=add" style="display: grid; gap: 15px; max-width: 500px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Medication Name:</label>
                <input type="text" name="name" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Quantity:</label>
                <input type="number" name="qty" min="1" max="10" value="1" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Time:</label>
                <select name="time" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="morning">🌅 Morning</option>
                    <option value="afternoon">☀️ Afternoon</option>
                    <option value="evening">🌙 Evening</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success" style="justify-self: start;">Add Medication</button>
        </form>
    </div>';

    // Current medications list
    $content .= '<h3>📋 Current Medications</h3>';

    if (empty($schedule)) {
        $content .= '<div class="empty-state">
            <p>No medications configured yet. Add your first medication above!</p>
        </div>';
    } else {
        $content .= '<div class="medication-list">';

        foreach ($schedule as $med) {
            if ($isEditing === $med['id']) {
                // Edit mode
                $content .= '<div class="medication-item" style="background: #fff3cd; border-color: #ffeaa7;">
                    <form method="POST" action="?page=setup&action=update" style="width: 100%; display: grid; gap: 15px;">
                        <input type="hidden" name="id" value="' . $med['id'] . '">
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Name:</label>
                                <input type="text" name="name" value="' . htmlspecialchars($med['name']) . '" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Quantity:</label>
                                <input type="number" name="qty" min="1" max="10" value="' . $med['qty'] . '" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Time:</label>
                                <select name="time" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="morning"' . ($med['time'] === 'morning' ? ' selected' : '') . '>🌅 Morning</option>
                                    <option value="afternoon"' . ($med['time'] === 'afternoon' ? ' selected' : '') . '>☀️ Afternoon</option>
                                    <option value="evening"' . ($med['time'] === 'evening' ? ' selected' : '') . '>🌙 Evening</option>
                                </select>
                            </div>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" class="btn btn-success">💾 Save</button>
                            <a href="?page=setup" class="btn btn-secondary" style="text-decoration: none;">❌ Cancel</a>
                        </div>
                    </form>
                </div>';
            } else {
                // Display mode
                $timeEmoji = match ($med['time']) {
                    'morning' => '🌅',
                    'afternoon' => '☀️',
                    'evening' => '🌙',
                    default => '🕐'
                };

                $content .= '<div class="medication-item">
                    <div class="medication-info">
                        <div class="medication-name">💊 ' . htmlspecialchars($med['name']) . '</div>
                        <div class="medication-details">
                            <span class="time-badge">' . $timeEmoji . ' ' . ucfirst($med['time']) . '</span>
                            Quantity: ' . (int)$med['qty'] . '
                        </div>
                    </div>
                    <div class="medication-actions">
                        <a href="?page=setup&edit=' . $med['id'] . '" class="btn btn-secondary">✏️ Edit</a>
                        <a href="?page=setup&action=delete&id=' . $med['id'] . '" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete ' . htmlspecialchars($med['name']) . '?\')">🗑️ Delete</a>
                    </div>
                </div>';
            }
        }

        $content .= '</div>';
    }

    // Back to schedule
    $content .= '<div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #e0e0e0; text-align: center;">
        <a href="?page=schedule" class="btn btn-success">📅 Back to Schedule</a>
    </div>';

    return $content;
}

// Handle GET actions only for specific schedule actions
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];

    // Only handle these specific GET actions
    if (in_array($action, ['mark_taken', 'mark_not_taken', 'reset_day', 'delete'])) {
        switch ($action) {
            case 'mark_taken':
                if (isset($_GET['id'])) {
                    markMedicationTaken((int)$_GET['id']);
                }
                break;

            case 'mark_not_taken':
                if (isset($_GET['id'])) {
                    markMedicationNotTaken((int)$_GET['id']);
                }
                break;

            case 'reset_day':
                resetDay();
                break;

            case 'delete':
                if (isset($_GET['id'])) {
                    $id = (int)$_GET['id'];

                    // Remove from schedule
                    $_SESSION['medcheck']['schedule'] = array_filter(
                        $_SESSION['medcheck']['schedule'],
                        function ($med) use ($id) {
                            return $med['id'] !== $id;
                        }
                    );

                    // Re-index array
                    $_SESSION['medcheck']['schedule'] = array_values($_SESSION['medcheck']['schedule']);

                    // Sync status after deleting
                    syncStatusWithSchedule();
                }
                break;
        }

        // Redirect to remove action from URL
        $page = $_GET['page'] ?? 'schedule';
        header("Location: ?page=" . $page);
        exit;
    }
}

// Handle POST actions for setup page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'add':
            if (isset($_POST['name'], $_POST['qty'], $_POST['time'])) {
                // Find the next ID
                $maxId = 0;
                foreach ($_SESSION['medcheck']['schedule'] as $med) {
                    $maxId = max($maxId, $med['id']);
                }

                // Add new medication
                $_SESSION['medcheck']['schedule'][] = [
                    'id' => $maxId + 1,
                    'name' => trim($_POST['name']),
                    'qty' => (int)$_POST['qty'],
                    'time' => $_POST['time']
                ];

                // Sync status after adding
                syncStatusWithSchedule();
            }
            break;

        case 'update':
            if (isset($_POST['id'], $_POST['name'], $_POST['qty'], $_POST['time'])) {
                $id = (int)$_POST['id'];

                // Update medication in schedule
                foreach ($_SESSION['medcheck']['schedule'] as &$med) {
                    if ($med['id'] === $id) {
                        $med['name'] = trim($_POST['name']);
                        $med['qty'] = (int)$_POST['qty'];
                        $med['time'] = $_POST['time'];
                        break;
                    }
                }

                // Sync status after updating
                syncStatusWithSchedule();
            }
            break;
    }

    // Force session save before redirect
    session_write_close();

    // Redirect to setup page with cache busting parameter
    $timestamp = time();
    header("Location: ?page=setup&refresh=" . $timestamp);
    exit;
}

// Generate and output the HTML
echo generateHTML();
