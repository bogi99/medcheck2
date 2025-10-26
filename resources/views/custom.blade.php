@extends('layouts.app')

@section('title', 'Schedule')

@section('content')
    <div class="max-w-3xl mx-auto">
        @if (!empty($schedule))
            @php
                $total = count($schedule);
                $taken = 0;
                foreach ($schedule as $pill) {
                    if (isset($status[$pill['id']]) && $status[$pill['id']]) {
                        $taken++;
                    }
                }
                $percent = $total ? round(($taken / $total) * 100) : 0;
            @endphp
            <div class="mb-6">
                <div class="flex justify-between items-center mb-1">
                    <span class="font-medium text-sm">Progress</span>
                    <span class="text-xs text-gray-500">{{ $taken }} / {{ $total }} pills taken</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                    <div class="bg-blue-500 h-4 rounded-full transition-all duration-300" style="width: {{ $percent }}%">
                    </div>
                </div>
                <div class="text-xs text-gray-500 mt-1 text-right">{{ $percent }}%</div>
            </div>
        @endif
        <h2 class="text-xl font-semibold mb-4">Today's Medication Schedule</h2>
        @if (empty($schedule))
            <div class="mb-4 text-gray-500">Nothing is setup yet. Please go to <a href="{{ route('setup') }}"
                    class="text-blue-600 underline">Setup</a> to add your pills.</div>
        @else
            <ul class="space-y-4 mb-6">
                @foreach ($schedule as $pill)
                    <li class="flex items-center justify-between bg-white dark:bg-gray-800 p-4 rounded shadow">
                        <div>
                            <span class="font-bold">{{ $pill['name'] }}</span>
                            <span class="ml-2 text-gray-600">x{{ $pill['qty'] }}</span>
                            <span class="ml-2 text-sm text-gray-400">({{ ucfirst($pill['time']) }})</span>
                        </div>
                        <form method="POST" action="{{ route('take', $pill['id']) }}">
                            @csrf
                            <button type="submit"
                                class="px-3 py-1 rounded {{ isset($status[$pill['id']]) && $status[$pill['id']] ? 'bg-green-500 text-white' : 'bg-blue-500 text-white hover:bg-blue-600' }}">
                                {{ isset($status[$pill['id']]) && $status[$pill['id']] ? 'Taken' : 'Take' }}
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
            <div class="flex justify-center mt-8">
                <form method="POST" action="{{ route('reset') }}" class="mt-8"
                    onsubmit="return confirm('Are you sure you want to reset today?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Reset
                        Day</button>
                </form>
            </div>
            <script>
                function showResetModal() {
                    document.getElementById('resetModal').style.display = 'flex';
                }

                function closeResetModal() {
                    document.getElementById('resetModal').style.display = 'none';
                }
            </script>
        @endif
    </div>
@endsection
