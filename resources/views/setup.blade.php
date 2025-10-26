@extends('layouts.app')

@section('title', 'Setup')

@section('content')
    <div class="max-w-3xl mx-auto">
        <h2 class="text-xl font-semibold mb-4">Setup Your Pills</h2>
        <form method="POST" action="{{ route('addPill') }}" class="mb-8 bg-white dark:bg-gray-800 p-6 rounded shadow">
            @csrf
            <div class="mb-4">
                <label class="block mb-1 font-medium" for="name">Pill Name</label>
                <input type="text" name="name" id="name" class="w-full px-3 py-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium" for="qty">Quantity</label>
                <input type="number" name="qty" id="qty" class="w-full px-3 py-2 border rounded" min="1"
                    required value="1">
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium" for="time">Time of Day</label>
                <select name="time" id="time" class="w-full px-3 py-2 border rounded" required>
                    <option value="morning">Morning</option>
                    <option value="afternoon">Afternoon</option>
                    <option value="evening">Evening</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Add Pill</button>
        </form>

        <h3 class="text-lg font-semibold mb-2">Current Pills</h3>
        @if (empty($schedule))
            <div class="mb-4 text-gray-500">No pills added yet.</div>
        @else
            <ul class="space-y-4">
                @foreach ($schedule as $pill)
                    <li class="flex items-center justify-between bg-white dark:bg-gray-800 p-4 rounded shadow">
                        <div>
                            <span class="font-bold">{{ $pill['name'] }}</span>
                            <span class="ml-2 text-gray-600">x{{ $pill['qty'] }}</span>
                            <span class="ml-2 text-sm text-gray-400">({{ ucfirst($pill['time']) }})</span>
                        </div>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('deletePill', $pill['id']) }}">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600">Delete</button>
                            </form>
                            <button type="button" class="px-3 py-1 rounded bg-yellow-500 text-white hover:bg-yellow-600"
                                onclick="showEditForm({{ $pill['id'] }}, '{{ addslashes($pill['name']) }}', {{ $pill['qty'] }}, '{{ $pill['time'] }}')">Edit</button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <!-- Edit Pill Modal -->
        <div id="editModal" class="fixed inset-0  items-center justify-center z-50 hidden">
            <div class="bg-gray-800 dark:bg-gray-900 p-6 rounded border-2 border-blue-400 w-full max-w-md text-white">
                <h3 class="text-lg font-semibold mb-4">Edit Pill</h3>
                <form id="editForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block mb-1 font-medium" for="edit_name">Pill Name</label>
                        <input type="text" name="name" id="edit_name" class="w-full px-3 py-2 border rounded"
                            required>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-1 font-medium" for="edit_qty">Quantity</label>
                        <input type="number" name="qty" id="edit_qty" class="w-full px-3 py-2 border rounded"
                            min="1" required>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-1 font-medium" for="edit_time">Time of Day</label>
                        <select name="time" id="edit_time" class="w-full px-3 py-2 border rounded" required>
                            <option value="morning">Morning</option>
                            <option value="afternoon">Afternoon</option>
                            <option value="evening">Evening</option>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-yellow-700 text-white rounded hover:bg-yellow-600">Save
                        Changes</button>
                    <button type="button" class="px-4 py-2 bg-gray-600 text-white rounded ml-2"
                        onclick="closeEditForm()">Cancel</button>
                </form>
            </div>
        </div>

        <script>
            function showEditForm(id, name, qty, time) {
                document.getElementById('editModal').style.display = 'flex';
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_qty').value = qty;
                document.getElementById('edit_time').value = time;
                document.getElementById('editForm').action = "{{ url('/edit-pill') }}/" + id;
            }

            function closeEditForm() {
                document.getElementById('editModal').style.display = 'none';
            }
        </script>
    </div>
@endsection
