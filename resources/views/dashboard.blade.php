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
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('stacks.exportAllExcel') }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow transition" title="طباعة/تصدير جميع الدفاتر في ملف إكسل واحد مرتبة بالتاريخ من الأقدم إلى الأحدث">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>طباعة الكل في ملف إكسل واحد (مرتب بالتاريخ)</span>
                    </a>
                    <a href="{{ route('stacks.exportAll') }}" class="inline-flex items-center gap-1.5 bg-gray-600 hover:bg-gray-700 text-white px-3.5 py-2 rounded-lg text-sm font-medium shadow transition" title="تحميل كل دفتر في ملف إكسل منفصل داخل أرشيف مضغوط">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>تحميل الكل (ZIP)</span>
                    </a>
                </div>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b">
                        <th class="p-3 text-sm font-bold text-gray-600">Stack Name</th>
                        <th class="p-3 text-sm font-bold text-gray-600">Serial Range</th>
                        <th class="p-3 text-sm font-bold text-gray-600">Bonds Status</th>
                        <th class="p-3 text-sm font-bold text-gray-600 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stacks as $stack)
                    <tr class="border-b hover:bg-blue-50/50 transition">
                        <td class="p-3 font-semibold">
                            <a href="{{ route('stacks.show', $stack->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1.5 font-bold">
                                <span>📁</span>
                                <span>{{ $stack->stack_name }}</span>
                            </a>
                        </td>
                        <td class="p-3 text-gray-600 font-mono text-sm">{{ $stack->start_serial }} - {{ $stack->end_serial }}</td>
                        <td class="p-3">
                            <div class="flex flex-wrap gap-1.5 items-center">
                                <span class="bg-blue-50 text-blue-700 border border-blue-200 text-xs px-2 py-0.5 rounded font-bold">
                                    {{ $stack->bonds_count }} سند
                                </span>
                                @if(($stack->cancelled_count ?? 0) > 0)
                                    <span class="bg-amber-50 text-amber-700 border border-amber-200 text-xs px-2 py-0.5 rounded font-bold">
                                        {{ $stack->cancelled_count }} ملغي
                                    </span>
                                @endif
                                @if(($stack->missing_count ?? 0) > 0)
                                    <span class="bg-red-50 text-red-700 border border-red-200 text-xs px-2 py-0.5 rounded font-bold">
                                        {{ $stack->missing_count }} مفقود
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="p-3 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('stacks.show', $stack->id) }}" class="inline-flex items-center gap-1 bg-blue-600 text-white px-3 py-1.5 rounded-md text-xs font-bold hover:bg-blue-700 shadow-sm transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    عرض السندات
                                </a>
                                <a href="/stacks/export/{{ $stack->id }}" class="inline-flex items-center gap-1 bg-green-600 text-white px-3 py-1.5 rounded-md text-xs font-bold hover:bg-green-700 shadow-sm transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Excel
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-400 italic">No stacks created yet. Digitized bonds first!</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection