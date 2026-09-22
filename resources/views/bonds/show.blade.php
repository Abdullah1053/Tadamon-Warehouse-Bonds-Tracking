@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto bg-white p-10 shadow-lg border border-gray-200" id="printableBond">
    <div class="flex justify-between border-b-2 border-black pb-4 mb-6">
        <div>
            <h1 class="text-xl font-bold">Material Delivery Bond</h1>
            <p class="text-sm text-gray-600">Warehouse Pro System</p>
        </div>
        <div class="text-right">
            <p class="text-red-600 font-bold text-2xl">№ {{ $bond->bond_serial }}</p>
            <p class="text-sm">Date: {{ $bond->date }}</p>
        </div>
    </div>

    @if ($bond->isCancelled())
        <div class="p-3 mb-6 bg-amber-100 text-amber-900 border-2 border-amber-300 rounded-lg text-center font-bold">
            ⚠️ هذا السند ملغي (النسخة الورقية موجودة بالدفتر)
        </div>
    @elseif ($bond->isMissing())
        <div class="p-3 mb-6 bg-red-100 text-red-900 border-2 border-red-300 rounded-lg text-center font-bold">
            ✂️ هذا السند مفقود (النسخة الورقية مقطوعة من الدفتر وغير موجودة)
        </div>
    @endif

    <div class="grid grid-cols-2 gap-y-4 mb-8">
        <div class="text-sm"><strong>Operation:</strong> {{ $bond->operation_name }}</div>
        <div class="text-sm"><strong>Vehicle:</strong> {{ $bond->car_number ?? 'N/A' }}</div>
        <div class="text-sm col-span-2"><strong>Received From:</strong> {{ $bond->received_from }}</div>
    </div>

    <table class="w-full border-collapse border border-black mb-10">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-black p-2 text-left">Description</th>
                <th class="border border-black p-2 w-32 text-center">Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bond->items as $item)
            <tr>
                <td class="border border-black p-2">{{ $item->item_description }}</td>
                <td class="border border-black p-2 text-center">{{ $item->quantity }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($bond->note)
    <div class="mt-4 p-3 bg-gray-50 border-l-4 border-gray-300 text-sm italic">
        <strong>Note:</strong> {{ $bond->note }}
    </div>
    @endif
    @if($bond->hasImage())
    <div class="mt-6 p-4 bg-gray-50 border rounded-xl">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-sm text-gray-800 flex items-center gap-1.5">
                <span>🖼️</span>
                <span>صورة السند الأصلية (Attached Bond Image):</span>
            </h3>
            <a href="{{ $bond->image_url }}" target="_blank" class="text-xs text-blue-600 hover:underline font-bold">
                فتح بالحجم الكامل ↗
            </a>
        </div>
        <div class="text-center bg-white p-2 rounded-lg border overflow-hidden">
            <a href="{{ $bond->image_url }}" target="_blank">
                <img src="{{ $bond->image_url }}" alt="Bond #{{ $bond->bond_serial }}" class="max-h-96 mx-auto rounded shadow-sm hover:opacity-95 transition">
            </a>
        </div>
    </div>
    @endif
    <div class="flex justify-between mt-20 italic text-sm">
        <div>Warehouse Signature: ________________</div>
        <div>Receiver Signature: ________________</div>
    </div>
</div>
<div class="max-w-3xl mx-auto mt-6 flex justify-between no-print">
    <a href="{{ route('bonds.index') }}" class="text-gray-600 underline">← Back to Archive</a>
    <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded font-bold">Print Bond</button>
</div>
@endsection