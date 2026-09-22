@extends('layouts.app')
@section('content')
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-md border-t-8 border-orange-500">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 mb-6 border-b border-gray-200 gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-800 tracking-tight">Edit Bond #{{ $bond->bond_serial }}</h2>
                <p class="text-sm text-gray-500">Update bond metadata, receiver information, or status.</p>
            </div>

            {{-- Mode Switcher Pills --}}
            @php
                $initialStatus = $bond->isMissing() ? 'missing' : ($bond->isCancelled() ? 'cancelled' : 'normal');
            @endphp
            <div class="inline-flex rounded-xl bg-gray-100 p-1 border border-gray-200 text-xs font-bold" id="editBondModeContainer">
                <button type="button" onclick="switchEditBondMode('normal')" id="btnEditModeNormal"
                    class="px-3.5 py-2 rounded-lg transition {{ $initialStatus === 'normal' ? 'bg-white text-green-700 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900' }} flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span>سند عادي</span>
                </button>
                <button type="button" onclick="switchEditBondMode('cancelled')" id="btnEditModeCancelled"
                    class="px-3.5 py-2 rounded-lg transition {{ $initialStatus === 'cancelled' ? 'bg-white text-amber-800 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900' }} flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>سند ملغي (ورقة موجودة)</span>
                </button>
                <button type="button" onclick="switchEditBondMode('missing')" id="btnEditModeMissing"
                    class="px-3.5 py-2 rounded-lg transition {{ $initialStatus === 'missing' ? 'bg-white text-red-700 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900' }} flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    <span>سند مفقود (مقطوع من الدفتر)</span>
                </button>
            </div>
        </div>

        {{-- Status Notification Banner --}}
        <div id="editStatusNotice" class="{{ $initialStatus === 'normal' ? 'hidden' : '' }} mb-6 p-4 rounded-xl text-sm font-semibold flex items-center gap-3 {{ $initialStatus === 'cancelled' ? 'bg-amber-50 border border-amber-200 text-amber-900' : 'bg-red-50 border border-red-200 text-red-900' }}">
            <span id="editStatusNoticeIcon">{{ $initialStatus === 'cancelled' ? '🚫' : '✂️' }}</span>
            <span id="editStatusNoticeText">
                @if($initialStatus === 'cancelled')
                    <strong>سند ملغي:</strong> الورقة الأصلية موجودة في الدفتر، وجميع الحقول النصية مضبوطة على "ملغي" وبدون بنود.
                @elseif($initialStatus === 'missing')
                    <strong>سند مفقود:</strong> الورقة مقطوعة من الدفتر، وجميع الحقول النصية مضبوطة على "مفقود" وبدون بنود.
                @endif
            </span>
        </div>

        <form id="editBondForm" class="space-y-6">
            @csrf @method('PUT')
            <input type="hidden" name="bond_type" id="hidden_edit_bond_type" value="{{ $initialStatus }}">
            <input type="hidden" name="is_missing" id="hidden_edit_is_missing" value="{{ $bond->is_missing ? 1 : 0 }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-5 rounded-xl border border-gray-200">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Serial</label>
                    <input type="text" name="bond_serial" value="{{ $bond->bond_serial }}" class="w-full border border-gray-300 p-2.5 rounded-lg font-bold text-gray-800 focus:ring-2 focus:ring-orange-400 focus:bg-white transition"
                        required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Date</label>
                    <input type="date" name="date" value="{{ $bond->date }}" class="w-full border border-gray-300 p-2.5 rounded-lg font-semibold focus:ring-2 focus:ring-orange-400 focus:bg-white transition"
                        required>
                </div>
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Operation Name</label>
                    <input type="text" name="operation_name" id="edit_field_operation_name" value="{{ $bond->operation_name }}" dir="auto"
                        class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 focus:ring-orange-400 focus:bg-white transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Received From</label>
                    <input type="text" name="received_from" id="edit_field_received_from" value="{{ $bond->received_from }}" dir="auto"
                        class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 focus:ring-orange-400 focus:bg-white transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Vehicle Number</label>
                    <input type="text" name="car_number" id="edit_field_car_number" value="{{ $bond->car_number }}"
                        class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 focus:ring-orange-400 focus:bg-white transition">
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Additional Notes</label>
                    <textarea name="note" id="edit_field_note" rows="2" dir="auto"
                        class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 focus:ring-orange-400 focus:bg-white transition">{{ $bond->note }}</textarea>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Bond Link / Image (رابط السند أو الصورة)</label>
                    <input type="text" name="bond_link" value="{{ $bond->bond_link }}"
                        class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 focus:ring-orange-400 focus:bg-white transition"
                        placeholder="https://... or link to bond image">
                </div>
            </div>

            {{-- Items Section --}}
            <div id="editItemsSection" class="{{ $initialStatus !== 'normal' ? 'hidden' : '' }}">
                <table id="itemsTable" class="w-full border-collapse rounded-lg overflow-hidden border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-left text-xs font-bold text-gray-600 uppercase">
                            <th class="p-3 border">Item Description</th>
                            <th class="p-3 border w-36">Quantity</th>
                            <th class="p-3 border w-16 text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bond->items as $index => $item)
                            <tr>
                                <td class="border p-1"><input type="text" name="items[{{ $index }}][description]"
                                        value="{{ $item->item_description }}" class="w-full p-2 outline-none edit-item-desc" dir="auto" required></td>
                                <td class="border p-1"><input type="number" name="items[{{ $index }}][quantity]" step="any"
                                        value="{{ $item->quantity }}" class="w-full p-2 outline-none edit-item-qty" required></td>
                                <td class="border text-center"><button type="button" onclick="removeRow(this)"
                                        class="text-red-500 font-bold hover:text-red-700 p-1">✕</button></td>
                            </tr>
                        @empty
                            <tr>
                                <td class="border p-1"><input type="text" name="items[0][description]"
                                        class="w-full p-2 outline-none edit-item-desc" placeholder="Item description..." dir="auto"></td>
                                <td class="border p-1"><input type="number" name="items[0][quantity]" step="any"
                                        class="w-full p-2 outline-none edit-item-qty" placeholder="0.00"></td>
                                <td class="border text-center"><button type="button" onclick="removeRow(this)"
                                        class="text-red-500 font-bold hover:text-red-700 p-1">✕</button></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Collapsed notice for Cancelled / Missing --}}
            <div id="editNoItemsNotice" class="{{ $initialStatus === 'normal' ? 'hidden' : '' }} p-6 text-center border-2 border-dashed border-gray-300 rounded-xl bg-gray-50/50">
                <p class="text-sm font-bold text-gray-600" id="editNoItemsNoticeText">
                    {{ $initialStatus === 'cancelled' ? 'سند ملغي: لا توجد مواد أو بنود.' : 'سند مفقود: لا توجد بنود.' }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">البنود غير مطلوبة ومفرغة تلقائياً.</p>
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                <button type="button" onclick="addRow()" id="editAddRowBtn" class="{{ $initialStatus !== 'normal' ? 'hidden' : '' }} px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-sm font-bold shadow transition">
                    + Add Row
                </button>
                <div class="flex gap-3 ml-auto">
                    <a href="{{ route('bonds.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-bold transition">
                        Cancel
                    </a>
                    <button type="submit" id="editSubmitBtn" class="px-8 py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-black shadow-lg transition">
                        UPDATE RECORD
                    </button>
                </div>
            </div>
        </form>
    </div>

   <script>
    let rowIdx = {{ max(1, $bond->items->count()) }};

    function switchEditBondMode(mode) {
        document.getElementById('hidden_edit_bond_type').value = mode;

        const btnNormal = document.getElementById('btnEditModeNormal');
        const btnCancelled = document.getElementById('btnEditModeCancelled');
        const btnMissing = document.getElementById('btnEditModeMissing');
        const notice = document.getElementById('editStatusNotice');
        const noticeIcon = document.getElementById('editStatusNoticeIcon');
        const noticeText = document.getElementById('editStatusNoticeText');
        const itemsSection = document.getElementById('editItemsSection');
        const noItemsNotice = document.getElementById('editNoItemsNotice');
        const noItemsNoticeText = document.getElementById('editNoItemsNoticeText');
        const addRowBtn = document.getElementById('editAddRowBtn');

        const opInput = document.getElementById('edit_field_operation_name');
        const recvInput = document.getElementById('edit_field_received_from');
        const noteInput = document.getElementById('edit_field_note');
        const carInput = document.getElementById('edit_field_car_number');
        const isMissingInput = document.getElementById('hidden_edit_is_missing');

        btnNormal.className = 'px-3.5 py-2 rounded-lg transition text-gray-600 hover:text-gray-900 flex items-center gap-1.5';
        btnCancelled.className = 'px-3.5 py-2 rounded-lg transition text-gray-600 hover:text-gray-900 flex items-center gap-1.5';
        btnMissing.className = 'px-3.5 py-2 rounded-lg transition text-gray-600 hover:text-gray-900 flex items-center gap-1.5';

        if (mode === 'normal') {
            btnNormal.className = 'px-3.5 py-2 rounded-lg transition bg-white text-green-700 shadow-sm font-bold flex items-center gap-1.5';
            notice.classList.add('hidden');
            itemsSection.classList.remove('hidden');
            noItemsNotice.classList.add('hidden');
            addRowBtn.classList.remove('hidden');
            enableEditItemInputs(true);

            if (opInput.value === 'ملغي' || opInput.value === 'مفقود') opInput.value = 'جسر النصر';
            if (recvInput.value === 'ملغي' || recvInput.value === 'مفقود') recvInput.value = '';
            if (noteInput.value === 'ملغي' || noteInput.value === 'مفقود') noteInput.value = 'رقم الفاتورة: ';
            isMissingInput.value = '0';

        } else if (mode === 'cancelled') {
            btnCancelled.className = 'px-3.5 py-2 rounded-lg transition bg-white text-amber-800 shadow-sm font-bold flex items-center gap-1.5';
            notice.className = 'mb-6 p-4 rounded-xl text-sm font-semibold flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-900';
            noticeIcon.innerHTML = '🚫';
            noticeText.innerHTML = '<strong>سند ملغي:</strong> الورقة الأصلية موجودة في الدفتر، وجميع الحقول النصية مضبوطة على "ملغي" وبدون بنود.';
            notice.classList.remove('hidden');

            itemsSection.classList.add('hidden');
            noItemsNotice.classList.remove('hidden');
            noItemsNoticeText.innerText = 'سند ملغي: لا توجد مواد أو بنود في هذا السند.';
            addRowBtn.classList.add('hidden');
            enableEditItemInputs(false);

            opInput.value = 'ملغي';
            recvInput.value = 'ملغي';
            noteInput.value = 'ملغي';
            carInput.value = '';
            isMissingInput.value = '0';

        } else if (mode === 'missing') {
            btnMissing.className = 'px-3.5 py-2 rounded-lg transition bg-white text-red-700 shadow-sm font-bold flex items-center gap-1.5';
            notice.className = 'mb-6 p-4 rounded-xl text-sm font-semibold flex items-center gap-3 bg-red-50 border border-red-200 text-red-900';
            noticeIcon.innerHTML = '✂️';
            noticeText.innerHTML = '<strong>سند مفقود:</strong> الورقة مقطوعة من الدفتر، وجميع الحقول النصية مضبوطة على "مفقود" وبدون بنود.';
            notice.classList.remove('hidden');

            itemsSection.classList.add('hidden');
            noItemsNotice.classList.remove('hidden');
            noItemsNoticeText.innerText = 'سند مفقود: الورقة مقطوعة ولا توجد بنود.';
            addRowBtn.classList.add('hidden');
            enableEditItemInputs(false);

            opInput.value = 'مفقود';
            recvInput.value = 'مفقود';
            noteInput.value = 'مفقود';
            carInput.value = '';
            isMissingInput.value = '1';
        }
    }

    function enableEditItemInputs(enabled) {
        document.querySelectorAll('.edit-item-desc, .edit-item-qty').forEach(input => {
            input.required = enabled;
            input.disabled = !enabled;
        });
    }

    function addRow() {
        const tableBody = document.querySelector('#itemsTable tbody');
        const newRow = `
            <tr class="hover:bg-gray-50 transition">
                <td class="border p-1">
                    <input type="text" name="items[${rowIdx}][description]" class="w-full p-2 outline-none edit-item-desc" required placeholder="Tool description..." dir="auto">
                </td>
                <td class="border p-1">
                    <input type="number" name="items[${rowIdx}][quantity]" step="any" class="w-full p-2 outline-none edit-item-qty" required placeholder="0.00">
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

        const saveBtn = document.getElementById('editSubmitBtn');
        saveBtn.disabled = true;
        saveBtn.innerText = 'UPDATING...';

        try {
            const response = await fetch('{{ route('bonds.update', $bond->id) }}', {
                method: 'POST',
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
