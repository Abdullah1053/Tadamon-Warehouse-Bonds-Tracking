@extends('layouts.app')
@section('content')
    @php
        $isDisbursement = ($type === 'disbursement');
        $themeBorder = $isDisbursement ? 'border-purple-600' : 'border-blue-600';
        $themeRing = $isDisbursement ? 'focus:ring-purple-400' : 'focus:ring-blue-400';
        $themeBadge = $isDisbursement ? 'bg-purple-100 text-purple-800 border-purple-200' : 'bg-blue-100 text-blue-800 border-blue-200';
        $themeBtn = $isDisbursement ? 'bg-purple-600 hover:bg-purple-700' : 'bg-green-600 hover:bg-green-700';
    @endphp

    <div class="max-w-4xl mx-auto mb-4">
        <!-- Top Bond Type Selector Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider px-2">نوع السند:</span>
                <div class="inline-flex rounded-lg bg-gray-100 p-1 border border-gray-200 text-xs font-bold">
                    <a href="{{ route('bonds.create', ['type' => 'receipt']) }}"
                        class="px-4 py-2 rounded-md transition flex items-center gap-2 {{ !$isDisbursement ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-200' }}">
                        <span>📥</span>
                        <span>سند استلام مواد (Receipt)</span>
                        @if(!$isDisbursement)
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                        @endif
                    </a>
                    <a href="{{ route('bonds.create', ['type' => 'disbursement']) }}"
                        class="px-4 py-2 rounded-md transition flex items-center gap-2 {{ $isDisbursement ? 'bg-purple-600 text-white shadow-sm' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-200' }}">
                        <span>📤</span>
                        <span>سند صرف مواد (Disbursement)</span>
                        @if($isDisbursement)
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                        @endif
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs">
                <span class="px-3 py-1.5 rounded-full font-bold border {{ $themeBadge }}">
                    {{ $isDisbursement ? 'دفتر صرف المواد المخزنية' : 'دفتر استلام وتوريد المواد' }}
                </span>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-md border-t-8 {{ $themeBorder }}">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 mb-6 border-b border-gray-200 gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl">{{ $isDisbursement ? '📤' : '📥' }}</span>
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight" id="formHeaderTitle">
                        {{ $isDisbursement ? 'إدخال سند صرف مواد (Disbursement Bond)' : 'إدخال سند استلام مواد (Receipt Bond)' }}
                    </h2>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $isDisbursement ? 'إدخال سندات صرف المواد من المستودع ومتابعتها في النظام.' : 'إدخال سندات توريد واستلام المواد إلى المستودع.' }}
                </p>
            </div>

            {{-- Mode Switcher Pills (Normal / Cancelled / Missing) --}}
            <div class="inline-flex rounded-xl bg-gray-100 p-1 border border-gray-200 text-xs font-bold" id="bondModeContainer">
                <button type="button" onclick="switchBondMode('normal')" id="btnModeNormal"
                    class="px-3.5 py-2 rounded-lg transition bg-white text-green-700 shadow-sm flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span>سند عادي (Normal)</span>
                </button>
                <button type="button" onclick="switchBondMode('cancelled')" id="btnModeCancelled"
                    class="px-3.5 py-2 rounded-lg transition text-gray-600 hover:text-gray-900 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>سند ملغي (ورقة موجودة)</span>
                </button>
                <button type="button" onclick="switchBondMode('missing')" id="btnModeMissing"
                    class="px-3.5 py-2 rounded-lg transition text-gray-600 hover:text-gray-900 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    <span>سند مفقود (مقطوع من الدفتر)</span>
                </button>
            </div>
        </div>

        {{-- Status Notification Banner (shown for Cancelled or Missing) --}}
        <div id="statusNotice" class="hidden mb-6 p-4 rounded-xl text-sm font-semibold flex items-center gap-3">
            <span id="statusNoticeIcon"></span>
            <span id="statusNoticeText"></span>
        </div>

        <form id="bondForm" class="space-y-6">
            @csrf
            {{-- Bond Type Hidden Input ('receipt' or 'disbursement') --}}
            <input type="hidden" name="type" id="hidden_bond_record_type" value="{{ $type }}">
            {{-- Status Mode Hidden Inputs --}}
            <input type="hidden" name="bond_type" id="hidden_bond_type" value="normal">
            <input type="hidden" name="is_missing" id="hidden_is_missing" value="0">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-5 rounded-xl border border-gray-200" id="headerFieldsGrid">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                        رقم السند (Bond Serial Number)
                    </label>
                    <input type="text" id="bond_serial" name="bond_serial" value="{{ $nextSerial }}"
                        class="w-full border border-gray-300 p-2.5 rounded-lg font-bold text-gray-800 focus:ring-2 {{ $themeRing }} focus:bg-white transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                        التاريخ (Date)
                    </label>
                    <input type="date" name="date" value="{{ $defaultDate }}" class="w-full border border-gray-300 p-2.5 rounded-lg font-semibold focus:ring-2 {{ $themeRing }} focus:bg-white transition"
                        required>
                </div>
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                        اسم العملية / المشروع (Operation / Project Name)
                    </label>
                    <input type="text" name="operation_name" id="field_operation_name" value="جسر النصر" dir="auto"
                        class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 {{ $themeRing }} focus:bg-white transition" placeholder="Project Site A"
                        required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1" id="label_party_name">
                        {{ $isDisbursement ? 'صُرف إلى / المستلم (Disbursed To / Receiver)' : 'وارد من / المورّد (Received From / Supplier)' }}
                    </label>
                    <input type="text" name="received_from" id="field_received_from" list="receivers_list" dir="auto"
                        class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 {{ $themeRing }} focus:bg-white transition" 
                        placeholder="{{ $isDisbursement ? 'اسم المستلم أو الجهة المصروف لها...' : 'اسم المورد أو الجهة الوارد منها...' }}"
                        autocomplete="off" required>

                    <datalist id="receivers_list">
                        @foreach ($receivers as $name)
                            <option value="{{ $name }}">
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                        رقم السيارة (Vehicle Number)
                    </label>
                    <input type="text" name="car_number" id="field_car_number" class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 {{ $themeRing }} focus:bg-white transition" placeholder="e.g. 12345/1">
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                        ملاحظات إضافية (Additional Notes)
                    </label>
                    <textarea name="note" id="field_note" rows="2" dir="auto"
                        class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 {{ $themeRing }} focus:bg-white transition"
                        placeholder="Any special instructions...">{{ $isDisbursement ? 'رقم إذن الصرف: ' : 'رقم الفاتورة: ' }}</textarea>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                        صورة السند الورقي (Bond Photo / Image)
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-blue-400 transition bg-gray-50/50" id="imageDropzone">
                        <input type="file" name="bond_image" id="field_bond_image" accept="image/*" class="hidden" onchange="previewSelectedImage(this)">
                        
                        <!-- Upload prompt -->
                        <div id="uploadPrompt" class="cursor-pointer" onclick="document.getElementById('field_bond_image').click()">
                            <div class="text-3xl mb-1">📷</div>
                            <div class="text-sm font-bold text-gray-700">اضغط هنا لاختيار صورة السند أو اسحبها إلى هنا</div>
                            <div class="text-xs text-gray-400 mt-1">يدعم JPG, PNG, WEBP (حتى 15 ميجابايت)</div>
                        </div>

                        <!-- Image Preview -->
                        <div id="imagePreviewContainer" class="hidden flex items-center justify-between bg-white p-2.5 rounded-lg border shadow-sm">
                            <div class="flex items-center gap-3">
                                <img id="imagePreviewImg" src="" alt="Bond Preview" class="w-16 h-16 object-cover rounded border">
                                <div class="text-left">
                                    <div id="imagePreviewName" class="text-xs font-bold text-gray-800 truncate max-w-xs"></div>
                                    <div id="imagePreviewDimensions" class="text-[11px] text-gray-400"></div>
                                </div>
                            </div>
                            <button type="button" onclick="clearSelectedImage()" class="text-xs font-bold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition">
                                ✕ إزالة الصورة
                            </button>
                        </div>

                        <!-- Optional external URL toggle -->
                        <div class="mt-2 text-right">
                            <button type="button" onclick="toggleUrlInput()" class="text-[11px] text-blue-600 hover:underline">
                                أو إدخال رابط خارجي (External Link)
                            </button>
                            <input type="text" name="bond_link" id="field_bond_link" class="hidden mt-1.5 w-full border border-gray-300 p-2 rounded-lg text-xs"
                                placeholder="https://... رابط صورة خارجي إن وجد">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items Section Container --}}
            <div id="itemsSection">
                <table id="itemsTable" class="w-full border-collapse rounded-lg overflow-hidden border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-left text-xs font-bold text-gray-600 uppercase">
                            <th class="p-3 border">Item Description (بيان المادة)</th>
                            <th class="p-3 border w-36">Quantity (الكمية)</th>
                            <th class="p-3 border w-16 text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border p-1">
                                <input type="text" name="items[0][description]"
                                    class="w-full p-2 outline-none item-desc-input" placeholder="Item description..." dir="auto" list="items_list" required>
                            </td>

                            <datalist id="items_list">
                                @foreach ($itemSuggestions as $desc)
                                    <option value="{{ $desc }}">
                                @endforeach
                            </datalist>
                            <td class="border p-1">
                                <input type="number" name="items[0][quantity]" step="any" class="w-full p-2 outline-none item-qty-input"
                                    placeholder="0.00" required>
                            </td>

                            <td class="border text-center">
                                <button type="button" onclick="removeRow(this)"
                                    class="text-red-500 font-bold hover:text-red-700 p-1">✕</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Collapsed placeholder for Cancelled / Missing modes --}}
            <div id="noItemsNotice" class="hidden p-6 text-center border-2 border-dashed border-gray-300 rounded-xl bg-gray-50/50">
                <p class="text-sm font-bold text-gray-600" id="noItemsNoticeText">لا توجد بنود لهذا السند.</p>
                <p class="text-xs text-gray-400 mt-0.5">البنود غير مطلوبة ومفرغة تلقائياً.</p>
            </div>

            {{-- Form Action Bar --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 pt-4 border-t border-gray-200">
                <div id="addToolContainer">
                    <button type="button" onclick="addRow()"
                        class="px-4 py-2.5 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-sm font-bold shadow-sm transition">
                        + Add Tool / Item (إضافة بند)
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-3 justify-end">
                    {{-- Quick Action: Cancelled (ملغي) --}}
                    <button type="button" onclick="saveCancelledBond()"
                        class="px-5 py-2.5 bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-300 rounded-lg font-bold text-sm transition shadow-sm flex items-center gap-1.5"
                        title="السند ملغي ولكن الورقة موجودة بالدفتر">
                        <span>🚫</span>
                        <span>MARK #<span class="current-serial-display">{{ $nextSerial }}</span> AS CANCELLED</span>
                    </button>

                    {{-- Quick Action: Missing (مفقود) --}}
                    <button type="button" onclick="saveMissingBond()"
                        class="px-5 py-2.5 bg-red-50 text-red-700 hover:bg-red-100 border border-red-300 rounded-lg font-bold text-sm transition shadow-sm flex items-center gap-1.5"
                        title="السند مقطوع من الدفتر وغير موجود">
                        <span>✂️</span>
                        <span>MARK #<span class="current-serial-display">{{ $nextSerial }}</span> AS MISSING</span>
                    </button>

                    {{-- Standard Save Button --}}
                    <button type="submit" id="mainSubmitBtn"
                        class="px-7 py-2.5 {{ $themeBtn }} text-white rounded-lg font-black text-sm shadow-md transition flex items-center gap-2">
                        <span>💾</span>
                        <span id="mainSubmitBtnText">
                            {{ $isDisbursement ? 'SAVE DISBURSEMENT BOND' : 'SAVE RECEIPT BOND' }}
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        let rowIdx = 1;
        let currentMode = 'normal'; // 'normal' | 'cancelled' | 'missing'
        const currentType = '{{ $type }}';
        const isDisbursement = (currentType === 'disbursement');

        // Function to switch bond entry mode (normal / cancelled / missing)
        function switchBondMode(mode) {
            currentMode = mode;
            document.getElementById('hidden_bond_type').value = mode;

            const btnNormal = document.getElementById('btnModeNormal');
            const btnCancelled = document.getElementById('btnModeCancelled');
            const btnMissing = document.getElementById('btnModeMissing');
            const statusNotice = document.getElementById('statusNotice');
            const statusNoticeIcon = document.getElementById('statusNoticeIcon');
            const statusNoticeText = document.getElementById('statusNoticeText');
            const itemsSection = document.getElementById('itemsSection');
            const noItemsNotice = document.getElementById('noItemsNotice');
            const noItemsNoticeText = document.getElementById('noItemsNoticeText');
            const addToolContainer = document.getElementById('addToolContainer');
            const submitBtn = document.getElementById('mainSubmitBtn');
            const submitBtnText = document.getElementById('mainSubmitBtnText');

            const opInput = document.getElementById('field_operation_name');
            const recvInput = document.getElementById('field_received_from');
            const noteInput = document.getElementById('field_note');
            const carInput = document.getElementById('field_car_number');
            const isMissingInput = document.getElementById('hidden_is_missing');

            // Reset pill styles
            btnNormal.className = 'px-3.5 py-2 rounded-lg transition text-gray-600 hover:text-gray-900 flex items-center gap-1.5';
            btnCancelled.className = 'px-3.5 py-2 rounded-lg transition text-gray-600 hover:text-gray-900 flex items-center gap-1.5';
            btnMissing.className = 'px-3.5 py-2 rounded-lg transition text-gray-600 hover:text-gray-900 flex items-center gap-1.5';

            if (mode === 'normal') {
                btnNormal.className = 'px-3.5 py-2 rounded-lg transition bg-white text-green-700 shadow-sm font-bold flex items-center gap-1.5';
                statusNotice.classList.add('hidden');
                itemsSection.classList.remove('hidden');
                noItemsNotice.classList.add('hidden');
                addToolContainer.classList.remove('hidden');
                enableItemInputs(true);

                opInput.value = 'جسر النصر';
                if (recvInput.value === 'ملغي' || recvInput.value === 'مفقود') recvInput.value = '';
                noteInput.value = isDisbursement ? 'رقم إذن الصرف: ' : 'رقم الفاتورة: ';
                isMissingInput.value = '0';

                submitBtn.className = isDisbursement 
                    ? 'px-7 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-black text-sm shadow-md transition flex items-center gap-2'
                    : 'px-7 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-black text-sm shadow-md transition flex items-center gap-2';
                submitBtnText.innerText = isDisbursement ? 'SAVE DISBURSEMENT BOND' : 'SAVE RECEIPT BOND';

            } else if (mode === 'cancelled') {
                btnCancelled.className = 'px-3.5 py-2 rounded-lg transition bg-white text-amber-800 shadow-sm font-bold flex items-center gap-1.5';
                statusNotice.className = 'mb-6 p-4 rounded-xl text-sm font-semibold flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-900';
                statusNoticeIcon.innerHTML = '🚫';
                statusNoticeText.innerHTML = '<strong>سند ملغي:</strong> الورقة الأصلية موجودة في الدفتر، وتم تعبئة جميع الحقول النصية تلقائياً بـ "ملغي" وبدون بنود.';
                statusNotice.classList.remove('hidden');

                itemsSection.classList.add('hidden');
                noItemsNotice.classList.remove('hidden');
                noItemsNoticeText.innerText = 'سند ملغي: لا توجد مواد أو بنود في هذا السند.';
                addToolContainer.classList.add('hidden');
                enableItemInputs(false);

                opInput.value = 'ملغي';
                recvInput.value = 'ملغي';
                noteInput.value = 'ملغي';
                carInput.value = '';
                isMissingInput.value = '0'; // Physical copy attached

                submitBtn.className = 'px-7 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-black text-sm shadow-md transition flex items-center gap-2';
                submitBtnText.innerText = 'SAVE CANCELLED BOND (ملغي)';

            } else if (mode === 'missing') {
                btnMissing.className = 'px-3.5 py-2 rounded-lg transition bg-white text-red-700 shadow-sm font-bold flex items-center gap-1.5';
                statusNotice.className = 'mb-6 p-4 rounded-xl text-sm font-semibold flex items-center gap-3 bg-red-50 border border-red-200 text-red-900';
                statusNoticeIcon.innerHTML = '✂️';
                statusNoticeText.innerHTML = '<strong>سند مفقود:</strong> الورقة مقطوعة من الدفتر وغير موجودة، وتم تعبئة جميع الحقول النصية تلقائياً بـ "مفقود" وبدون بنود.';
                statusNotice.classList.remove('hidden');

                itemsSection.classList.add('hidden');
                noItemsNotice.classList.remove('hidden');
                noItemsNoticeText.innerText = 'سند مفقود: الورقة مقطوعة ولا توجد بنود.';
                addToolContainer.classList.add('hidden');
                enableItemInputs(false);

                opInput.value = 'مفقود';
                recvInput.value = 'مفقود';
                noteInput.value = 'مفقود';
                carInput.value = '';
                isMissingInput.value = '1'; // Physical copy missing

                submitBtn.className = 'px-7 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-black text-sm shadow-md transition flex items-center gap-2';
                submitBtnText.innerText = 'SAVE MISSING BOND (مفقود)';
            }
        }

        function enableItemInputs(enabled) {
            document.querySelectorAll('.item-desc-input, .item-qty-input').forEach(input => {
                input.required = enabled;
                input.disabled = !enabled;
            });
        }

        // Add a new tool row
        function addRow() {
            const tableBody = document.querySelector('#itemsTable tbody');
            const newRow = `
                <tr class="hover:bg-gray-50 transition">
                    <td class="border p-1"><input type="text" name="items[${rowIdx}][description]" list="items_list" class="w-full p-2 outline-none item-desc-input" required placeholder="Item description..." dir="auto"></td>
                    <td class="border p-1"><input type="number" name="items[${rowIdx}][quantity]" step="any" class="w-full p-2 outline-none item-qty-input" required placeholder="0.00"></td>
                    <td class="border text-center"><button type="button" onclick="removeRow(this)" class="text-red-500 font-black hover:text-red-700 px-2">✕</button></td>
                </tr>`;
            tableBody.insertAdjacentHTML('beforeend', newRow);
            rowIdx++;
        }

        function removeRow(btn) {
            if (document.querySelectorAll('#itemsTable tbody tr').length > 1) {
                btn.closest('tr').remove();
            } else {
                alert("At least one item is required for normal bonds.");
            }
        }

        // Handle Quick Action: Cancelled Bond
        async function saveCancelledBond() {
            const serial = document.getElementById('bond_serial').value.trim();
            const date = document.querySelector('input[name="date"]').value;

            if (!confirm(`هل تؤكد أن السند رقم #${serial} ملغي (النسخة الورقية موجودة بالدفتر)؟`)) return;

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('type', currentType);
            formData.append('bond_serial', serial);
            formData.append('date', date);
            formData.append('operation_name', 'ملغي');
            formData.append('received_from', 'ملغي');
            formData.append('note', 'ملغي');
            formData.append('car_number', '');
            formData.append('bond_type', 'cancelled');
            formData.append('is_missing', '0');

            await submitVoidBond(formData, serial, 'ملغي');
        }

        // Handle Quick Action: Missing Bond
        async function saveMissingBond() {
            const serial = document.getElementById('bond_serial').value.trim();
            const date = document.querySelector('input[name="date"]').value;

            if (!confirm(`هل تؤكد أن السند رقم #${serial} مفقود (الورقة مقطوعة من الدفتر وغير موجودة)؟`)) return;

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('type', currentType);
            formData.append('bond_serial', serial);
            formData.append('date', date);
            formData.append('operation_name', 'مفقود');
            formData.append('received_from', 'مفقود');
            formData.append('note', 'مفقود');
            formData.append('car_number', '');
            formData.append('bond_type', 'missing');
            formData.append('is_missing', '1');

            await submitVoidBond(formData, serial, 'مفقود');
        }

        async function submitVoidBond(formData, serial, label) {
            try {
                const response = await fetch('/bonds/store', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const result = await response.json();
                if (result.success) {
                    const bondTypeArabic = (currentType === 'disbursement') ? 'سند الصرف' : 'سند الاستلام';
                    alert(`تم تسجيل ${bondTypeArabic} رقم #${serial} كـ "${label}" بنجاح!`);
                    advanceToNextBond(result);
                } else {
                    alert('Error saving record. Check server logs.');
                }
            } catch (error) {
                console.error(error);
                alert('Communication error while saving bond.');
            }
        }

        // Standard Form Submission
        document.getElementById('bondForm').onsubmit = async (e) => {
            e.preventDefault();
            const saveBtn = document.getElementById('mainSubmitBtn');
            saveBtn.disabled = true;
            const originalText = saveBtn.innerText;
            saveBtn.innerText = 'SAVING...';

            const formData = new FormData(e.target);
            if (!formData.has('type')) {
                formData.append('type', currentType);
            }

            try {
                const response = await fetch('/bonds/store', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const result = await response.json();

                if (result.success) {
                    const newName = formData.get('received_from');
                    const datalist = document.getElementById('receivers_list');
                    if (datalist && newName && !['--- N/A ---', 'مفقود', 'ملغي'].includes(newName)) {
                        const existingOptions = Array.from(datalist.options).map(opt => opt.value);
                        if (!existingOptions.includes(newName)) {
                            const newOption = document.createElement('option');
                            newOption.value = newName;
                            datalist.appendChild(newOption);
                        }
                    }

                    const bondTypeArabic = (currentType === 'disbursement') ? 'سند الصرف' : 'سند الاستلام';
                    alert(`تم حفظ ${bondTypeArabic} رقم #${document.getElementById('bond_serial').value} بنجاح!`);
                    advanceToNextBond(result);
                } else {
                    alert('Error saving bond. Please verify fields.');
                }
            } catch (error) {
                alert('Error saving bond. Check console.');
                console.error(error);
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerText = originalText;
            }
        };

        function advanceToNextBond(result) {
            const savedDate = result.saved_date || document.querySelector('input[name="date"]').value;
            const form = document.getElementById('bondForm');
            form.reset();

            // Clear image preview
            clearSelectedImage();

            // Set next serial & keep date
            document.querySelector('input[name="date"]').value = savedDate;
            document.getElementById('bond_serial').value = result.next_serial;
            document.querySelectorAll('.current-serial-display').forEach(el => el.innerText = result.next_serial);

            // Re-assert bond type
            document.getElementById('hidden_bond_record_type').value = currentType;

            // Always return to normal mode for the next bond
            switchBondMode('normal');

            // Reset items table
            document.querySelector('#itemsTable tbody').innerHTML = `
                <tr>
                    <td class="border p-1"><input type="text" name="items[0][description]" list="items_list" class="w-full p-2 outline-none item-desc-input" required placeholder="Item description..." dir="auto"></td>
                    <td class="border p-1"><input type="number" name="items[0][quantity]" step="any" class="w-full p-2 outline-none item-qty-input" required placeholder="0.00"></td>
                    <td class="border text-center"><button type="button" onclick="removeRow(this)" class="text-red-500 font-bold hover:text-red-700 p-1">✕</button></td>
                </tr>`;
            rowIdx = 1;
        }

        // Image Preview & Upload Helpers
        function previewSelectedImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreviewImg').src = e.target.result;
                    document.getElementById('imagePreviewName').textContent = file.name;
                    document.getElementById('imagePreviewDimensions').textContent = (file.size / 1024).toFixed(1) + ' KB';
                    document.getElementById('imagePreviewContainer').classList.remove('hidden');
                    document.getElementById('uploadPrompt').classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        function clearSelectedImage() {
            const input = document.getElementById('field_bond_image');
            if (input) input.value = '';
            const previewContainer = document.getElementById('imagePreviewContainer');
            if (previewContainer) previewContainer.classList.add('hidden');
            const prompt = document.getElementById('uploadPrompt');
            if (prompt) prompt.classList.remove('hidden');
        }

        function toggleUrlInput() {
            const urlInput = document.getElementById('field_bond_link');
            if (urlInput) {
                urlInput.classList.toggle('hidden');
                if (!urlInput.classList.contains('hidden')) {
                    urlInput.focus();
                }
            }
        }

        // Live serial update for button labels
        document.getElementById('bond_serial').addEventListener('input', (e) => {
            document.querySelectorAll('.current-serial-display').forEach(el => el.innerText = e.target.value);
        });
    </script>
@endsection
