@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- BREADCRUMB & TOP HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">لوحة التحكم (Dashboard)</a>
                <span>/</span>
                <span class="text-blue-600">دفتر: {{ $stack->stack_name }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-gray-800 tracking-tight flex items-center gap-2">
                    <span>📁</span>
                    <span>{{ $stack->stack_name }}</span>
                </h1>
                <span class="bg-gray-100 text-gray-700 text-xs px-2.5 py-1 rounded-md font-mono font-bold border">
                    النطاق: #{{ $stack->start_serial }} - #{{ $stack->end_serial }}
                </span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3.5 py-2 rounded-lg text-sm font-bold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                العودة للدفاتر
            </a>
            <a href="{{ route('stacks.export', $stack->id) }}" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                تحميل إكسل (Excel)
            </a>
            <a href="{{ route('bonds.uploadImagesView', ['stack_id' => $stack->id]) }}" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white px-3.5 py-2 rounded-lg text-sm font-bold shadow-sm transition">
                <span>📷</span>
                <span>رفع صور الدفتر</span>
            </a>
            <a href="{{ route('bonds.create') }}" class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                إضافة سند جديد
            </a>
        </div>
    </div>

    <!-- STATS CARDS (INTERACTIVE FILTER SWITCHERS) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- ALL -->
        <button type="button" onclick="filterByStatus('all')" id="stat-card-all" class="stat-card text-left bg-white p-4 rounded-xl shadow-sm border-2 border-blue-600 hover:shadow-md transition">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">إجمالي السندات</div>
            <div class="text-3xl font-black text-gray-800 mt-1" id="count-total">{{ $stats['total'] }}</div>
            <div class="text-xs text-blue-600 font-semibold mt-1">عرض كل السندات</div>
        </button>

        <!-- NORMAL -->
        <button type="button" onclick="filterByStatus('normal')" id="stat-card-normal" class="stat-card text-left bg-white p-4 rounded-xl shadow-sm border-2 border-transparent hover:border-emerald-500 hover:shadow-md transition">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">سندات سليمة</div>
            <div class="text-3xl font-black text-emerald-600 mt-1" id="count-normal">{{ $stats['normal'] }}</div>
            <div class="text-xs text-emerald-600 font-semibold mt-1">سندات معتمدة ومسجلة</div>
        </button>

        <!-- CANCELLED -->
        <button type="button" onclick="filterByStatus('cancelled')" id="stat-card-cancelled" class="stat-card text-left bg-white p-4 rounded-xl shadow-sm border-2 border-transparent hover:border-amber-500 hover:shadow-md transition">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">سندات ملغية (ورقة موجودة)</div>
            <div class="text-3xl font-black text-amber-600 mt-1" id="count-cancelled">{{ $stats['cancelled'] }}</div>
            <div class="text-xs text-amber-600 font-semibold mt-1">ملغي مع وجود الأصل الورقي</div>
        </button>

        <!-- MISSING -->
        <button type="button" onclick="filterByStatus('missing')" id="stat-card-missing" class="stat-card text-left bg-white p-4 rounded-xl shadow-sm border-2 border-transparent hover:border-red-500 hover:shadow-md transition">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">سندات مفقودة (ورقة مقطوعة)</div>
            <div class="text-3xl font-black text-red-600 mt-1" id="count-missing">{{ $stats['missing'] }}</div>
            <div class="text-xs text-red-600 font-semibold mt-1">مقطوع أو مفقود من الدفتر</div>
        </button>
    </div>

    <!-- FILTER & SEARCH BAR -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative w-full md:w-80">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" id="searchInput" placeholder="بحث بالرقم، المستلم، العملية..." 
                   class="w-full pl-9 pr-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
        </div>

        <!-- Filter Status Buttons -->
        <div class="flex items-center gap-1.5 w-full md:w-auto overflow-x-auto">
            <span class="text-xs font-bold text-gray-400 ml-1">تصفية:</span>
            <button type="button" onclick="filterByStatus('all')" id="filter-btn-all" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-600 text-white transition">الكل</button>
            <button type="button" onclick="filterByStatus('normal')" id="filter-btn-normal" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition">سليم</button>
            <button type="button" onclick="filterByStatus('cancelled')" id="filter-btn-cancelled" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition">ملغي</button>
            <button type="button" onclick="filterByStatus('missing')" id="filter-btn-missing" class="filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition">مفقود</button>
        </div>

        <!-- Visible count indicator -->
        <div class="text-xs font-semibold text-gray-500">
            يتم عرض <span id="visibleCount" class="font-bold text-gray-800">{{ $bonds->count() }}</span> من أصل <span class="font-bold text-gray-800">{{ $bonds->count() }}</span> سند
        </div>
    </div>

    <!-- STICKY / FLOATING BULK ACTIONS TOOLBAR -->
    <div id="bulkActionsBar" class="hidden sticky top-4 z-40 bg-slate-900 text-white p-3.5 rounded-xl shadow-2xl border border-slate-700 flex flex-wrap items-center justify-between gap-3 transition-all duration-200">
        <div class="flex items-center gap-3">
            <span class="bg-blue-600 text-white text-xs font-black px-2.5 py-1 rounded-full flex items-center gap-1">
                <span>📌</span>
                <span id="selectedCountBadge">0</span>
                <span>محدد</span>
            </span>
            <button type="button" onclick="deselectAll()" class="text-xs text-slate-300 hover:text-white underline font-semibold transition">
                إلغاء التحديد
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Mark as Cancelled -->
            <button type="button" onclick="submitBulkAction('cancel')" class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-3 py-1.5 rounded-lg text-xs shadow transition">
                <span>🚫</span>
                <span>تحويل إلى ملغي</span>
            </button>

            <!-- Mark as Missing -->
            <button type="button" onclick="submitBulkAction('missing')" class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white font-bold px-3 py-1.5 rounded-lg text-xs shadow transition">
                <span>✂️</span>
                <span>تحويل إلى مفقود</span>
            </button>

            <!-- Restore to Normal -->
            <button type="button" onclick="submitBulkAction('normal')" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1.5 rounded-lg text-xs shadow transition">
                <span>🔄</span>
                <span>استعادة لسليم</span>
            </button>

            <!-- Unstack -->
            <button type="button" onclick="submitBulkAction('unstack')" class="inline-flex items-center gap-1.5 bg-orange-600 hover:bg-orange-700 text-white font-bold px-3 py-1.5 rounded-lg text-xs shadow transition">
                <span>🔓</span>
                <span>إلغاء التكديس</span>
            </button>

            <!-- Move to another stack -->
            @if($otherStacks->count() > 0)
            <div class="inline-flex items-center bg-slate-800 rounded-lg p-0.5 border border-slate-700">
                <select id="targetStackSelect" class="bg-transparent text-xs text-white px-2 py-1 focus:outline-none">
                    <option value="" class="bg-slate-900 text-slate-400">نقل إلى دفتر آخر...</option>
                    @foreach($otherStacks as $otherStack)
                        <option value="{{ $otherStack->id }}" class="bg-slate-900 text-white">{{ $otherStack->stack_name }} ({{ $otherStack->start_serial }}-{{ $otherStack->end_serial }})</option>
                    @endforeach
                </select>
                <button type="button" onclick="submitBulkMove()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-2.5 py-1 rounded-md transition">
                    نقل
                </button>
            </div>
            @endif

            <!-- Delete Selected -->
            <button type="button" onclick="submitBulkAction('delete')" class="inline-flex items-center gap-1.5 bg-rose-700 hover:bg-rose-800 text-white font-bold px-3 py-1.5 rounded-lg text-xs shadow transition">
                <span>🗑️</span>
                <span>حذف نهائي</span>
            </button>
        </div>
    </div>

    <!-- BONDS DATA TABLE -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="bondsTable">
                <thead class="bg-gray-50 border-b text-xs font-bold text-gray-500 uppercase">
                    <tr>
                        <th class="p-3 w-10 text-center">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this.checked)" 
                                   class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="p-3">رقم السند</th>
                        <th class="p-3">التاريخ</th>
                        <th class="p-3">المستلم</th>
                        <th class="p-3">العملية / المشروع</th>
                        <th class="p-3">المواد والأصناف</th>
                        <th class="p-3">الرابط</th>
                        <th class="p-3 text-right">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100" id="bondsTableBody">
                    @forelse($bonds as $bond)
                    @php
                        $status = $bond->status; // 'cancelled', 'missing', 'normal'
                        $statusClass = $status === 'missing' 
                            ? 'bg-red-50/40 hover:bg-red-50' 
                            : ($status === 'cancelled' ? 'bg-amber-50/40 hover:bg-amber-50' : 'hover:bg-blue-50/30');
                    @endphp
                    <tr class="bond-row transition {{ $statusClass }}" 
                        id="bond-row-{{ $bond->id }}" 
                        data-id="{{ $bond->id }}" 
                        data-serial="{{ $bond->bond_serial }}"
                        data-status="{{ $status }}"
                        data-search="{{ strtolower($bond->bond_serial . ' ' . $bond->received_from . ' ' . $bond->operation_name . ' ' . $bond->car_number . ' ' . $bond->note) }}">
                        
                        <!-- Checkbox -->
                        <td class="p-3 text-center">
                            <input type="checkbox" value="{{ $bond->id }}" onchange="handleRowCheckboxChange(this)"
                                   class="bond-checkbox w-4 h-4 rounded text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </td>

                        <!-- Serial & Status Badge -->
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                <span class="font-black font-mono {{ $status === 'missing' ? 'text-red-700' : ($status === 'cancelled' ? 'text-amber-700' : 'text-blue-700') }}">
                                    #{{ $bond->bond_serial }}
                                </span>
                                @if($status === 'missing')
                                    <span class="bg-red-100 text-red-800 border border-red-200 px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider">
                                        مفقود
                                    </span>
                                @elseif($status === 'cancelled')
                                    <span class="bg-amber-100 text-amber-800 border border-amber-200 px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider">
                                        ملغي
                                    </span>
                                @else
                                    <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-1.5 py-0.5 rounded text-[10px] font-bold">
                                        سليم
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Date -->
                        <td class="p-3 text-gray-600 font-mono text-xs whitespace-nowrap">
                            {{ $bond->date }}
                        </td>

                        <!-- Receiver -->
                        <td class="p-3 font-semibold">
                            @if($status === 'missing')
                                <span class="text-red-600 font-bold">مفقود</span>
                            @elseif($status === 'cancelled')
                                <span class="text-amber-700 font-bold">ملغي</span>
                            @else
                                <span class="text-gray-800">{{ $bond->received_from }}</span>
                            @endif
                        </td>

                        <!-- Operation -->
                        <td class="p-3 text-gray-700 text-xs">
                            @if($status === 'missing' || $status === 'cancelled')
                                <span class="text-gray-400 italic">{{ $bond->operation_name }}</span>
                            @else
                                <div class="font-medium text-gray-800">{{ $bond->operation_name }}</div>
                                @if($bond->car_number)
                                    <div class="text-[11px] text-gray-400 font-mono">لوحة: {{ $bond->car_number }}</div>
                                @endif
                            @endif
                        </td>

                        <!-- Materials & Items -->
                        <td class="p-3">
                            @if($status === 'cancelled')
                                <span class="text-amber-600 text-xs font-semibold italic">--- سند ملغي ---</span>
                            @elseif($status === 'missing')
                                <span class="text-red-600 text-xs font-semibold italic">--- سند مفقود ---</span>
                            @else
                                @if($bond->items->count() > 0)
                                    <div class="flex items-center gap-1.5" title="{{ $bond->items->map(fn($i) => $i->item_description . ' (' . $i->quantity . ')')->implode(', ') }}">
                                        <span class="bg-blue-50 text-blue-700 font-bold px-1.5 py-0.5 rounded text-[11px] border border-blue-200">
                                            {{ $bond->items->count() }} أصناف
                                        </span>
                                        <span class="text-xs text-gray-500 truncate max-w-[200px] block">
                                            {{ $bond->items->first()->item_description }}
                                            @if($bond->items->count() > 1)
                                                وغيرها...
                                            @endif
                                        </span>
                                    </div>
                                @else
                                    <span class="text-gray-300 text-xs">-</span>
                                @endif
                            @endif
                        </td>

                        <!-- Link / Image -->
                        <td class="p-3">
                            @if($bond->hasImage())
                                <button type="button" onclick="openImageModal('{{ $bond->image_url }}', '{{ $bond->bond_serial }}', '{{ addslashes($bond->received_from) }}')" 
                                        class="inline-flex items-center gap-1 text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2 py-1 rounded text-xs font-bold transition shadow-xs">
                                    <span>🖼️</span>
                                    <span>صورة</span>
                                </button>
                            @else
                                <span class="text-gray-300 text-xs">-</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="p-3 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('bonds.show', $bond->id) }}" class="text-blue-600 hover:text-blue-800 text-xs font-bold">
                                    عرض
                                </a>
                                <a href="{{ route('bonds.edit', $bond->id) }}" class="text-amber-600 hover:text-amber-800 text-xs font-bold">
                                    تعديل
                                </a>
                                <button type="button" onclick="singleUnstack({{ $bond->id }}, '{{ $bond->bond_serial }}')" 
                                        class="text-orange-500 hover:text-orange-700 text-xs font-semibold">
                                    فك
                                </button>
                                <button type="button" onclick="singleDelete({{ $bond->id }}, '{{ $bond->bond_serial }}')" 
                                        class="text-rose-600 hover:text-rose-800 text-xs font-bold">
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="noBondsRow">
                        <td colspan="8" class="p-12 text-center text-gray-400 italic">
                            لا توجد سندات في هذا الدفتر حالياً.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JAVASCRIPT LOGIC -->
<script>
let currentStatusFilter = 'all';
let lastCheckedCheckbox = null;

// Filter table rows by status ('all', 'normal', 'cancelled', 'missing')
function filterByStatus(status) {
    currentStatusFilter = status;

    // Update filter buttons appearance
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('bg-gray-100', 'text-gray-700');
    });
    const activeBtn = document.getElementById('filter-btn-' + status);
    if (activeBtn) {
        activeBtn.classList.remove('bg-gray-100', 'text-gray-700');
        activeBtn.classList.add('bg-blue-600', 'text-white');
    }

    // Update stat cards border appearance
    document.querySelectorAll('.stat-card').forEach(card => {
        card.classList.remove('border-blue-600', 'border-emerald-500', 'border-amber-500', 'border-red-500');
        card.classList.add('border-transparent');
    });
    const activeCard = document.getElementById('stat-card-' + status);
    if (activeCard) {
        activeCard.classList.remove('border-transparent');
        if (status === 'all') activeCard.classList.add('border-blue-600');
        else if (status === 'normal') activeCard.classList.add('border-emerald-500');
        else if (status === 'cancelled') activeCard.classList.add('border-amber-500');
        else if (status === 'missing') activeCard.classList.add('border-red-500');
    }

    applyFilters();
}

// Search input debounce and apply
document.getElementById('searchInput').addEventListener('input', function() {
    applyFilters();
});

function applyFilters() {
    const query = document.getElementById('searchInput').value.trim().toLowerCase();
    const rows = document.querySelectorAll('.bond-row');
    let visible = 0;

    rows.forEach(row => {
        const rowStatus = row.dataset.status;
        const rowSearch = row.dataset.search || '';

        const matchesStatus = (currentStatusFilter === 'all') || (rowStatus === currentStatusFilter);
        const matchesQuery = !query || rowSearch.includes(query);

        if (matchesStatus && matchesQuery) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('visibleCount').textContent = visible;
    updateSelectAllState();
}

// Multi-select logic with shift-click support
function handleRowCheckboxChange(clickedCheckbox) {
    if (event && event.shiftKey && lastCheckedCheckbox && lastCheckedCheckbox !== clickedCheckbox) {
        const visibleCheckboxes = Array.from(document.querySelectorAll('.bond-row:not([style*="display: none"]) .bond-checkbox'));
        const start = visibleCheckboxes.indexOf(lastCheckedCheckbox);
        const end = visibleCheckboxes.indexOf(clickedCheckbox);
        if (start !== -1 && end !== -1) {
            const [min, max] = [Math.min(start, end), Math.max(start, end)];
            for (let i = min; i <= max; i++) {
                visibleCheckboxes[i].checked = clickedCheckbox.checked;
            }
        }
    }
    lastCheckedCheckbox = clickedCheckbox;
    updateBulkActionsBar();
    updateSelectAllState();
}

function toggleSelectAll(checked) {
    const visibleCheckboxes = document.querySelectorAll('.bond-row:not([style*="display: none"]) .bond-checkbox');
    visibleCheckboxes.forEach(cb => cb.checked = checked);
    updateBulkActionsBar();
}

function deselectAll() {
    document.querySelectorAll('.bond-checkbox').forEach(cb => cb.checked = false);
    const selectAll = document.getElementById('selectAllCheckbox');
    if (selectAll) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    }
    updateBulkActionsBar();
}

function updateSelectAllState() {
    const selectAll = document.getElementById('selectAllCheckbox');
    const visibleCheckboxes = Array.from(document.querySelectorAll('.bond-row:not([style*="display: none"]) .bond-checkbox'));
    
    if (visibleCheckboxes.length === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
        return;
    }

    const checkedCount = visibleCheckboxes.filter(cb => cb.checked).length;
    if (checkedCount === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    } else if (checkedCount === visibleCheckboxes.length) {
        selectAll.checked = true;
        selectAll.indeterminate = false;
    } else {
        selectAll.checked = false;
        selectAll.indeterminate = true;
    }
}

function updateBulkActionsBar() {
    const selected = document.querySelectorAll('.bond-checkbox:checked');
    const bar = document.getElementById('bulkActionsBar');
    const badge = document.getElementById('selectedCountBadge');

    badge.textContent = selected.length;
    if (selected.length > 0) {
        bar.classList.remove('hidden');
    } else {
        bar.classList.add('hidden');
    }
}

// Bulk Actions Dispatcher
async function submitBulkAction(action) {
    const selected = Array.from(document.querySelectorAll('.bond-checkbox:checked'));
    const ids = selected.map(cb => parseInt(cb.value));

    if (ids.length === 0) {
        alert('يرجى تحديد سند واحد على الأقل.');
        return;
    }

    const actionLabels = {
        cancel: 'تحويل السندات المحددة إلى "ملغي"؟\n(سيتم إفراغ الأصناف وتعيين الحقول كملغي مع الاحتفاظ بالنسخة الورقية)',
        missing: 'تحويل السندات المحددة إلى "مفقود"؟\n(سيتم إفراغ الأصناف وتعيين الحقول كمفقود لأن الورقة مقطوعة)',
        normal: 'استعادة السندات المحددة إلى الحالة الطبيعية؟',
        unstack: 'إلغاء تكديس السندات المحددة وإعادتها لقائمة غير المخصصة؟',
        delete: '⚠️ تحذير: هل أنت متأكد من حذف هذه السندات نهائياً من قاعدة البيانات؟ لا يمكن التراجع عن هذا الإجراء!'
    };

    if (!confirm(`هل أنت متأكد من تطبيق: ${actionLabels[action] || action} على ${ids.length} سند؟`)) {
        return;
    }

    await executeBulkApi({ bond_ids: ids, action: action });
}

async function submitBulkMove() {
    const selected = Array.from(document.querySelectorAll('.bond-checkbox:checked'));
    const ids = selected.map(cb => parseInt(cb.value));
    const targetStackId = document.getElementById('targetStackSelect').value;

    if (ids.length === 0) {
        alert('يرجى تحديد سند واحد على الأقل.');
        return;
    }
    if (!targetStackId) {
        alert('يرجى اختيار الدفتر الهدف لنقل السندات إليه.');
        return;
    }

    const targetStackText = document.getElementById('targetStackSelect').options[document.getElementById('targetStackSelect').selectedIndex].text;
    if (!confirm(`هل أنت متأكد من نقل ${ids.length} سند إلى: ${targetStackText}؟`)) {
        return;
    }

    await executeBulkApi({ bond_ids: ids, action: 'move', target_stack_id: targetStackId });
}

async function executeBulkApi(payload) {
    try {
        const response = await fetch('{{ route("bonds.bulkAction") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (data.success) {
            alert(data.message);
            // Smoothly reload page to accurately recompute all stats and relationships
            window.location.reload();
        } else {
            alert(data.message || 'حدث خطأ أثناء تنفيذ الإجراء.');
        }
    } catch (err) {
        console.error(err);
        alert('حدث خطأ غير متوقع أثناء الاتصال بالخادم.');
    }
}

// Single Unstack & Delete shortcuts
function singleUnstack(bondId, serial) {
    if (!confirm(`هل أنت متأكد من إلغاء تكديس السند #${serial}؟`)) return;
    executeBulkApi({ bond_ids: [bondId], action: 'unstack' });
}

function singleDelete(bondId, serial) {
    if (!confirm(`⚠️ هل أنت متأكد من حذف السند #${serial} نهائياً؟`)) return;
    executeBulkApi({ bond_ids: [bondId], action: 'delete' });
}

// Lightbox Modal Functions
function openImageModal(url, serial, receiver) {
    document.getElementById('modalTitle').textContent = `صورة السند #${serial} ${receiver ? '- ' + receiver : ''}`;
    document.getElementById('modalImg').src = url;
    document.getElementById('modalExternalLink').href = url;
    document.getElementById('imageLightboxModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageLightboxModal').classList.add('hidden');
    document.getElementById('modalImg').src = '';
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeImageModal();
});
</script>

<!-- IMAGE LIGHTBOX MODAL -->
<div id="imageLightboxModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4" onclick="closeImageModal()">
    <div class="relative bg-white rounded-2xl max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl border flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-4 border-b bg-gray-50">
            <div class="flex items-center gap-2">
                <span class="text-lg">🖼️</span>
                <span class="font-bold text-gray-800 text-sm" id="modalTitle">صورة السند</span>
            </div>
            <div class="flex items-center gap-2">
                <a id="modalExternalLink" href="#" target="_blank" class="text-xs text-blue-600 hover:underline font-bold px-2.5 py-1 bg-blue-50 rounded">
                    فتح بالحجم الكامل ↗
                </a>
                <button type="button" onclick="closeImageModal()" class="text-gray-400 hover:text-gray-700 text-lg font-bold px-2 py-0.5 rounded-md hover:bg-gray-200">
                    ✕
                </button>
            </div>
        </div>
        <div class="p-4 overflow-auto flex items-center justify-center bg-gray-900/5 min-h-[300px]">
            <img id="modalImg" src="" alt="Bond Image" class="max-h-[75vh] max-w-full object-contain rounded shadow">
        </div>
    </div>
</div>
@endsection
