@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <!-- LEFT: Create Stack Section -->
    <div class="md:col-span-1 bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-600 self-start">
        <h3 class="text-xl font-bold mb-4 text-gray-800">1. Group into Stack</h3>
        <p class="text-sm text-gray-500 mb-4">Enter a range of Bond Serial numbers to group them for export.</p>
        
        <form action="/stacks/create" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-gray-400">Stack Name</label>
                <input type="text" name="stack_name" class="w-full border p-2 rounded" placeholder="e.g. Site A - Phase 2" required>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400">Serial From</label>
                    <input type="number" name="start" class="w-full border p-2 rounded" required>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400">Serial To</label>
                    <input type="number" name="end" class="w-full border p-2 rounded" required>
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded font-bold hover:bg-blue-700 transition">
                Create & Link Bonds
            </button>
        </form>
    </div>

    <!-- RIGHT: Available Stacks Section -->
    <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800">2. Exported Stacks</h3>
            @if($stacks->count() > 0)
                <a href="{{ route('stacks.exportAll') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download All (ZIP)
                </a>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b">
                        <th class="p-3 text-sm font-bold text-gray-600">Stack Name</th>
                        <th class="p-3 text-sm font-bold text-gray-600">Serial Range</th>
                        <th class="p-3 text-sm font-bold text-gray-600 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stacks as $stack)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 font-medium">{{ $stack->stack_name }}</td>
                        <td class="p-3 text-gray-600">{{ $stack->start_serial }} - {{ $stack->end_serial }}</td>
                        <td class="p-3 text-right">
                            <a href="/stacks/export/{{ $stack->id }}" class="inline-block bg-green-500 text-white px-4 py-1 rounded text-sm font-bold hover:bg-green-600">
                                Download Excel
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="p-8 text-center text-gray-400 italic">No stacks created yet. Digitized bonds first!</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection