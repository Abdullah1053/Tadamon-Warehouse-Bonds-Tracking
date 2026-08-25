@extends('layouts.app')
@section('content')
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md border-t-8 border-blue-600">
        <h2 class="text-2xl font-bold mb-6 text-gray-700">Digitize Delivery Bond</h2>

        <form id="bondForm" class="space-y-6">
            @csrf
            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded border">
                <div>
                    <label class="block text-sm font-bold text-gray-600">Bond Serial Number</label>
                    <input type="number" id="bond_serial" name="bond_serial" value="{{ $nextSerial }}"
                        class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-600">Date</label>
                    <input type="date" name="date" value="{{ $defaultDate }}" class="w-full border p-2 rounded"
                        required>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-600">Operation / Project Name</label>
                    <input type="text" name="operation_name" value="جسر النصر"
                        class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" placeholder="Project Site A"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-600">Received From (Name)</label>
                    <input type="text" name="received_from" class="w-full border p-2 rounded" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-600">Vehicle Number</label>
                    <input type="text" name="car_number" class="w-full border p-2 rounded">
                </div>


                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-600">Additional Notes</label>
                    <textarea name="note" rows="2" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400"
                        placeholder="Any special instructions...">رقم الفاتورة: </textarea>
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
                    <tr>
                        <td class="border"><input type="text" name="items[0][description]"
                                class="w-full p-2 outline-none" required></td>
                        <td class="border"><input type="number" name="items[0][quantity]" class="w-full p-2 outline-none"
                                required></td>
                        <td class="border text-center"><button type="button" onclick="removeRow(this)"
                                class="text-red-500 font-bold">X</button></td>
                    </tr>
                </tbody>
            </table>

            <div class="flex justify-between items-center gap-4">
                <button type="button" onclick="addRow()"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">+ Add Tool</button>

                <div class="flex gap-2">
                    <!-- New Button -->
                    <button type="button" onclick="saveMissingBond()"
                        class="bg-red-100 text-red-700 px-6 py-3 rounded-lg font-bold border border-red-300 hover:bg-red-200">
                        MARK #<span id="missing_num_display">{{ $nextSerial }}</span> AS MISSING
                    </button>

                    <button type="submit"
                        class="bg-green-600 text-white px-8 py-3 rounded-lg font-bold shadow-lg hover:bg-green-700">SAVE
                        BOND</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        let rowIdx = 1;

        // Function to add a new tool row
        function addRow() {
            const tableBody = document.querySelector('#itemsTable tbody');
            const newRow = `
        <tr class="hover:bg-gray-50 transition">
            <td class="border"><input type="text" name="items[${rowIdx}][description]" class="w-full p-2 outline-none" required placeholder="Tool description..."></td>
            <td class="border"><input type="number" name="items[${rowIdx}][quantity]" class="w-full p-2 outline-none" required placeholder="0"></td>
            <td class="border text-center"><button type="button" onclick="removeRow(this)" class="text-red-500 font-black hover:text-red-700 px-2">✕</button></td>
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

        // AJAX Submission
        document.getElementById('bondForm').onsubmit = async (e) => {
            e.preventDefault();
            const saveBtn = e.target.querySelector('button[type="submit"]');
            saveBtn.disabled = true;
            saveBtn.innerText = 'SAVING...';

            const formData = new FormData(e.target);

            try {
                const response = await fetch('/bonds/store', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    // Success Feedback
                    alert('Bond #' + document.getElementById('bond_serial').value + ' saved successfully!');

                    // Prepare for next paper
                    e.target.reset();
                    document.querySelector('textarea[name="note"]').value = "رقم الفاتورة: ";
                    document.querySelector('input[name="operation_name"]').value = "جسر النصر";

                    document.getElementById('bond_serial').value = result.next_serial;

                    // Reset items table to 1 row
                    document.querySelector('#itemsTable tbody').innerHTML = `
                <tr>
                    <td class="border"><input type="text" name="items[0][description]" class="w-full p-2 outline-none" required></td>
                    <td class="border"><input type="number" name="items[0][quantity]" class="w-full p-2 outline-none" required></td>
                    <td class="border text-center"><button type="button" onclick="removeRow(this)" class="text-red-500 font-bold">X</button></td>
                </tr>`;
                    rowIdx = 1;
                }
            } catch (error) {
                alert('Error saving bond. Check console.');
                console.error(error);
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerText = 'SAVE BOND';
            }
        };
    </script>
    <script>
        async function saveMissingBond() {
            const serial = document.getElementById('bond_serial').value;
            const date = document.querySelector('input[name="date"]').value;

            if (!confirm(`Confirm serial #${serial} is missing from the physical stack?`)) return;

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('bond_serial', serial);
            formData.append('date', date);
            formData.append('operation_name', '--- MISSING ---');
            formData.append('received_from', '--- N/A ---');
            formData.append('is_missing', 1);
            formData.append('note', 'This bond was missing from the physical sequence.');
            // No items sent

            const response = await fetch('/bonds/store', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();
            if (result.success) {
                alert(`Serial #${serial} recorded as missing.`);
                document.getElementById('bondForm').reset();
                document.getElementById('bond_serial').value = result.next_serial;
                document.getElementById('missing_num_display').innerText = result.next_serial;
            }
        }

        // Update the display number when user manually types a serial
        document.getElementById('bond_serial').addEventListener('input', (e) => {
            document.getElementById('missing_num_display').innerText = e.target.value;
        });
    </script>
@endsection
