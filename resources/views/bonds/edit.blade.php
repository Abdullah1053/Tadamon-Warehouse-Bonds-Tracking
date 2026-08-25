@extends('layouts.app')
@section('content')
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md border-t-8 border-orange-500">
        <h2 class="text-2xl font-bold mb-6 text-gray-700">Edit Bond #{{ $bond->bond_serial }}</h2>

        <form id="editBondForm" class="space-y-6">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded border">
                <div>
                    <label class="block text-sm font-bold text-gray-600">Serial</label>
                    <input type="number" name="bond_serial" value="{{ $bond->bond_serial }}" class="w-full border p-2 rounded"
                        required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-600">Date</label>
                    <input type="date" name="date" value="{{ $bond->date }}" class="w-full border p-2 rounded"
                        required>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-600">Operation Name</label>
                    <input type="text" name="operation_name" value="{{ $bond->operation_name }}"
                        class="w-full border p-2 rounded" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-600">Received From</label>
                    <input type="text" name="received_from" value="{{ $bond->received_from }}"
                        class="w-full border p-2 rounded" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-600">Vehicle Number</label>
                    <input type="text" name="car_number" value="{{ $bond->car_number }}"
                        class="w-full border p-2 rounded">
                </div>
                <!-- Inside the grid div of the form -->
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-600">Additional Notes</label>
                    <textarea name="note" rows="2" class="w-full border p-2 rounded focus:ring-2 focus:ring-orange-400">{{ $bond->note }}</textarea>
                </div>
            </div>

            <table id="itemsTable" class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-left">
                        <th class="p-2 border">Item Description</th>
                        <th class="p-2 border w-32">Quantity</th>
                        <th class="p-2 border w-16"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bond->items as $index => $item)
                        <tr>
                            <td class="border"><input type="text" name="items[{{ $index }}][description]"
                                    value="{{ $item->item_description }}" class="w-full p-2 outline-none" required></td>
                            <td class="border"><input type="number" name="items[{{ $index }}][quantity]"
                                    value="{{ $item->quantity }}" class="w-full p-2 outline-none" required></td>
                            <td class="border text-center"><button type="button" onclick="removeRow(this)"
                                    class="text-red-500 font-bold">X</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="flex justify-between items-center">
                <button type="button" onclick="addRow()" class="bg-gray-500 text-white px-4 py-2 rounded">+ Add
                    Row</button>
                <button type="submit" class="bg-orange-600 text-white px-8 py-3 rounded-lg font-bold shadow-lg">UPDATE
                    RECORD</button>
            </div>
        </form>
    </div>

   <script>
    // Initialize rowIdx based on the number of existing items to avoid index collisions
    let rowIdx = {{ $bond->items->count() }};

    function addRow() {
        const tableBody = document.querySelector('#itemsTable tbody');
        const newRow = `
            <tr class="hover:bg-gray-50 transition">
                <td class="border">
                    <input type="text" name="items[${rowIdx}][description]" class="w-full p-2 outline-none" required placeholder="Tool description...">
                </td>
                <td class="border">
                    <input type="number" name="items[${rowIdx}][quantity]" class="w-full p-2 outline-none" required placeholder="0">
                </td>
                <td class="border text-center">
                    <button type="button" onclick="removeRow(this)" class="text-red-500 font-black hover:text-red-700 px-2">✕</button>
                </td>
            </tr>`;
        tableBody.insertAdjacentHTML('beforeend', newRow);
        rowIdx++;
    }

    function removeRow(btn) {
        if (document.querySelectorAll('#itemsTable tbody tr').length > 1) {
            btn.closest('tr').remove();
        } else {
            alert("At least one item is required.");
        }
    }

    document.getElementById('editBondForm').onsubmit = async (e) => {
        e.preventDefault();

        const saveBtn = e.target.querySelector('button[type="submit"]');
        saveBtn.disabled = true;
        saveBtn.innerText = 'UPDATING...';

        try {
            const response = await fetch('{{ route('bonds.update', $bond->id) }}', {
                method: 'POST', // standard POST used; Laravel spoofs PUT via the hidden _method input
                body: new FormData(e.target),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();
            if (result.success) {
                window.location.href = result.redirect;
            } else {
                alert('Update failed. Please check your input.');
            }
        } catch (error) {
            console.error('Submission error:', error);
            alert('An error occurred while updating the record.');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerText = 'UPDATE RECORD';
        }
    };
</script>
@endsection
