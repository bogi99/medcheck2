@extends('layouts.app')

@section('title', 'Export / Import')

@section('content')
    <div class="max-w-3xl mx-auto">
        <h2 class="text-xl font-semibold mb-4">Export / Import Setup</h2>
        <div class="mb-8 bg-white dark:bg-gray-800 p-6 rounded shadow">
            <h3 class="text-lg font-semibold mb-2">Export</h3>
            <form method="POST" action="{{ route('exportData') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Export as
                    JSON</button>
            </form>
        </div>
        <div class="mb-8 bg-white dark:bg-gray-800 p-6 rounded shadow">
            <h3 class="text-lg font-semibold mb-2">Import</h3>
            <form method="POST" action="{{ route('importData') }}">
                @csrf
                <label class="block mb-2 font-medium" for="import_json">Paste JSON here:</label>
                <textarea name="import_json" id="import_json" rows="6" class="w-full px-3 py-2 border rounded mb-4" required></textarea>
                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Import</button>
            </form>
        </div>
        <div class="text-lg text-red-600 font-bold mt-6">Importing will <u>overwrite</u> your current setup and progress.
        </div>
    </div>
@endsection
