@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">لوحة التحكم (Dashboard)</a>
                <span>/</span>
                <span class="text-blue-600 font-bold">رافع صور السندات المحلي</span>
            </div>
            <h1 class="text-2xl font-black text-gray-800 tracking-tight flex items-center gap-2">
                <span>📷</span>
                <span>رفع صور السندات إلى المشروع مباشرة</span>
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                ارفع صور السندات محلياً بدون الحاجة لمواقع أو خدمات خارجية (مثل ImageKit). يتم حفظ الصور في خادم المشروع وربطها تلقائياً بأرقام السندات المطابقة.
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if($selectedStack)
                <a href="{{ route('stacks.show', $selectedStack->id) }}" class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3.5 py-2 rounded-lg text-xs font-bold transition">
                    ← العودة لدفتر: {{ $selectedStack->stack_name }}
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3.5 py-2 rounded-lg text-xs font-bold transition">
                    ← العودة للوحة التحكم
                </a>
            @endif
        </div>
    </div>

    <!-- UPLOAD CONFIG CARD -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-6">
        <!-- Target Stack Selector -->
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                1. حدد الدفتر المستهدف (اختياري)
            </label>
            <div class="max-w-md">
                <select id="targetStack" class="w-full border border-gray-300 p-2.5 rounded-lg text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none font-medium">
                    <option value="">🔍 بحث في جميع السندات تلقائياً (بناءً على رقم السند في اسم الملف)</option>
                    @foreach($stacks as $s)
                        <option value="{{ $s->id }}" {{ ($selectedStackId == $s->id) ? 'selected' : '' }}>
                            📁 {{ $s->stack_name }} (النطاق: #{{ $s->start_serial }} - #{{ $s->end_serial }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-400 mt-1">
                    * يتم استخراج رقم السند تلقائياً من اسم الملف (مثال: <code class="bg-gray-100 px-1 py-0.5 rounded text-gray-700 font-mono">03001.jpg</code> أو <code class="bg-gray-100 px-1 py-0.5 rounded text-gray-700 font-mono">IMG_3002.png</code>).
                </p>
            </div>
        </div>

        <!-- DROPZONE -->
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                2. اختر صور السندات أو اسحبها هنا
            </label>
            
            <div id="dropzoneBox" 
                 class="border-3 border-dashed border-gray-300 hover:border-blue-500 rounded-2xl p-8 text-center bg-gray-50/70 hover:bg-blue-50/30 transition cursor-pointer"
                 onclick="document.getElementById('fileInput').click()">
                
                <input type="file" id="fileInput" multiple accept="image/*,.avif,.pdf" class="hidden" onchange="handleFilesSelected(this.files)">
                
                <div class="max-w-md mx-auto space-y-2">
                    <div class="text-5xl">📁</div>
                    <div class="text-base font-black text-gray-800">
                        اسحب وأفلت صور السندات هنا، أو اضغط للتصفح
                    </div>
                    <div class="text-xs text-gray-500">
                        يمكنك تحديد عشرات أو مئات الصور دفعة واحدة (JPG, PNG, WEBP). الحجم الأقصى: 20 ميجابايت لكل صورة.
                    </div>
                </div>
            </div>
        </div>

        <!-- SELECTED FILES STAGING / QUEUE -->
        <div id="stagingArea" class="hidden space-y-4 pt-4 border-t">
            <div class="flex items-center justify-between">
                <div class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <span>📋 الملفات المحددة للرفع:</span>
                    <span id="stagedCount" class="bg-blue-100 text-blue-800 text-xs px-2.5 py-0.5 rounded-full font-black">0</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="clearStaging()" class="text-xs text-red-600 hover:text-red-800 font-semibold px-2 py-1">
                        إلغاء التحديد
                    </button>
                    <button type="button" id="startUploadBtn" onclick="startBulkUpload()" 
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-black text-sm px-6 py-2.5 rounded-lg shadow-md hover:shadow-lg transition">
                        <span>🚀</span>
                        <span>بدء رفع وربط الصور الآن</span>
                    </button>
                </div>
            </div>

            <!-- Pre-upload thumbnail strip -->
            <div id="thumbnailsGrid" class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2 max-h-48 overflow-y-auto p-2 bg-gray-50 rounded-lg border">
                <!-- Staged previews populated via JS -->
            </div>
        </div>

        <!-- PROGRESS BAR -->
        <div id="progressContainer" class="hidden space-y-2 pt-4 border-t">
            <div class="flex justify-between items-center text-xs font-bold">
                <span id="progressStatus" class="text-blue-700">جاري الرفع والمعالجة...</span>
                <span id="progressPercent" class="font-mono text-gray-700">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden shadow-inner">
                <div id="progressBar" class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <!-- UPLOAD RESULTS SUMMARY & TABLE -->
    <div id="resultsCard" class="hidden bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-6">
        <div class="flex items-center justify-between border-b pb-4">
            <h2 class="text-lg font-black text-gray-800 flex items-center gap-2">
                <span>🎉</span>
                <span>نتيجة عملية الرفع والربط</span>
            </h2>
            <div class="flex items-center gap-2">
                <button type="button" onclick="resetUploader()" class="text-xs font-bold text-blue-600 hover:underline">
                    + رفع دفعة جديدة
                </button>
            </div>
        </div>

        <!-- Results Badges -->
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-blue-50 border border-blue-200 p-4 rounded-xl text-center">
                <div class="text-xs font-bold text-blue-600 uppercase">إجمالي المرفوع</div>
                <div id="resTotal" class="text-2xl font-black text-blue-800 mt-1">0</div>
            </div>
            <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl text-center">
                <div class="text-xs font-bold text-emerald-600 uppercase">تم ربطه بالسند بنجاح</div>
                <div id="resMatched" class="text-2xl font-black text-emerald-800 mt-1">0</div>
            </div>
            <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl text-center">
                <div class="text-xs font-bold text-amber-600 uppercase">غير مطابق (حُفظ فقط)</div>
                <div id="resUnmatched" class="text-2xl font-black text-amber-800 mt-1">0</div>
            </div>
        </div>

        <!-- Results Details Table -->
        <div class="overflow-x-auto border rounded-xl">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-gray-50 border-b font-bold text-gray-500 uppercase">
                    <tr>
                        <th class="p-3 w-16 text-center">معاينة</th>
                        <th class="p-3">اسم الملف</th>
                        <th class="p-3">الرقم المستخرج</th>
                        <th class="p-3">السند المرتبط</th>
                        <th class="p-3">المستلم</th>
                        <th class="p-3 text-right">الحالة</th>
                    </tr>
                </thead>
                <tbody id="resultsTableBody" class="divide-y divide-gray-100">
                    <!-- Populated via JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JAVASCRIPT LOGIC -->
<script>
let stagedFiles = [];

// Drag and drop event listeners
const dropzone = document.getElementById('dropzoneBox');

['dragenter', 'dragover'].forEach(eventName => {
    dropzone.addEventListener(eventName, (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.add('border-blue-500', 'bg-blue-50/50');
    });
});

['dragleave', 'drop'].forEach(eventName => {
    dropzone.addEventListener(eventName, (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('border-blue-500', 'bg-blue-50/50');
    });
});

dropzone.addEventListener('drop', (e) => {
    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        handleFilesSelected(e.dataTransfer.files);
    }
});

function handleFilesSelected(fileList) {
    const files = Array.from(fileList);
    if (files.length === 0) return;

    stagedFiles = files;
    document.getElementById('stagedCount').textContent = files.length;
    document.getElementById('stagingArea').classList.remove('hidden');

    const grid = document.getElementById('thumbnailsGrid');
    grid.innerHTML = '';

    files.slice(0, 24).forEach(file => {
        const item = document.createElement('div');
        item.className = 'relative group border rounded bg-white p-1 text-center';
        
        const img = document.createElement('img');
        img.className = 'w-full h-12 object-cover rounded';
        img.alt = file.name;

        const reader = new FileReader();
        reader.onload = (e) => img.src = e.target.result;
        reader.readAsDataURL(file);

        const nameLabel = document.createElement('div');
        nameLabel.className = 'text-[9px] text-gray-500 truncate mt-1';
        nameLabel.textContent = file.name;

        item.appendChild(img);
        item.appendChild(nameLabel);
        grid.appendChild(item);
    });

    if (files.length > 24) {
        const more = document.createElement('div');
        more.className = 'flex items-center justify-center bg-gray-100 rounded text-xs font-bold text-gray-500';
        more.textContent = `+ ${files.length - 24} ملف إضافي`;
        grid.appendChild(more);
    }
}

function clearStaging() {
    stagedFiles = [];
    document.getElementById('fileInput').value = '';
    document.getElementById('stagingArea').classList.add('hidden');
    document.getElementById('thumbnailsGrid').innerHTML = '';
}

function uploadBatchChunk(formData, onProgress) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route("bonds.bulkUpload") }}', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');

        if (onProgress && xhr.upload) {
            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    onProgress(e.loaded, e.total);
                }
            };
        }

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    resolve(data);
                } catch (parseErr) {
                    reject(new Error('استجابة غير صالحة من الخادم.'));
                }
            } else {
                let msg = 'فشلت عملية الرفع.';
                try {
                    const errRes = JSON.parse(xhr.responseText);
                    if (errRes.message) msg = errRes.message;
                    if (errRes.errors) {
                        const firstKey = Object.keys(errRes.errors)[0];
                        if (firstKey && errRes.errors[firstKey][0]) {
                            msg = errRes.errors[firstKey][0];
                        }
                    }
                } catch (e) {}
                reject(new Error(msg));
            }
        };

        xhr.onerror = function() {
            reject(new Error('حدث خطأ في الاتصال بالشبكة أثناء الرفع.'));
        };

        xhr.send(formData);
    });
}

async function startBulkUpload() {
    if (stagedFiles.length === 0) {
        alert('يرجى اختيار صور أولاً.');
        return;
    }

    const startBtn = document.getElementById('startUploadBtn');
    startBtn.disabled = true;

    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    const progressStatus = document.getElementById('progressStatus');
    
    progressContainer.classList.remove('hidden');

    // BATCH_SIZE = 15 ensures we NEVER exceed PHP's default max_file_uploads (20)
    // or request post_max_size limits on servers!
    const BATCH_SIZE = 15;
    const totalFiles = stagedFiles.length;
    const totalBatches = Math.ceil(totalFiles / BATCH_SIZE);

    const aggregatedData = {
        total_count: 0,
        matched_count: 0,
        unmatched_count: 0,
        details: []
    };

    const stackId = document.getElementById('targetStack').value;
    const csrfToken = '{{ csrf_token() }}';

    try {
        for (let bIndex = 0; bIndex < totalBatches; bIndex++) {
            const startIdx = bIndex * BATCH_SIZE;
            const endIdx = Math.min(startIdx + BATCH_SIZE, totalFiles);
            const batchFiles = stagedFiles.slice(startIdx, endIdx);

            const batchLabel = totalBatches > 1 
                ? `الدفعة ${bIndex + 1} من ${totalBatches} (الصور ${startIdx + 1} إلى ${endIdx} من ${totalFiles})`
                : `${totalFiles} صورة`;

            startBtn.innerHTML = `<span>⏳</span><span>جاري الرفع... (${startIdx}/${totalFiles})</span>`;
            progressStatus.textContent = `جاري رفع ومعالجة ${batchLabel}...`;

            const batchFormData = new FormData();
            batchFormData.append('_token', csrfToken);
            if (stackId) {
                batchFormData.append('stack_id', stackId);
            }
            batchFiles.forEach(file => {
                batchFormData.append('images[]', file);
            });

            // Upload this batch with smooth progress calculation
            const batchResult = await uploadBatchChunk(batchFormData, (loaded, total) => {
                const batchBase = (bIndex / totalBatches) * 100;
                const batchFraction = (loaded / total) * (100 / totalBatches);
                const overallPercent = Math.min(99, Math.round(batchBase + batchFraction));
                progressBar.style.width = overallPercent + '%';
                progressPercent.textContent = overallPercent + '%';
            });

            aggregatedData.total_count += batchResult.total_count || batchFiles.length;
            aggregatedData.matched_count += batchResult.matched_count || 0;
            aggregatedData.unmatched_count += batchResult.unmatched_count || 0;
            if (batchResult.details && Array.isArray(batchResult.details)) {
                aggregatedData.details.push(...batchResult.details);
            }

            const currentPercent = Math.round(((bIndex + 1) / totalBatches) * 100);
            progressBar.style.width = currentPercent + '%';
            progressPercent.textContent = currentPercent + '%';
        }

        // All batches finished
        progressBar.style.width = '100%';
        progressPercent.textContent = '100%';
        progressStatus.textContent = `اكتمل رفع ومعالجة جميع الصور (${totalFiles}) بنجاح!`;

        startBtn.disabled = false;
        startBtn.innerHTML = `<span>🚀</span><span>بدء رفع وربط الصور الآن</span>`;

        displayResults(aggregatedData);

    } catch (err) {
        console.error(err);
        startBtn.disabled = false;
        startBtn.innerHTML = `<span>🚀</span><span>بدء رفع وربط الصور الآن</span>`;
        alert(err.message || 'حدث خطأ أثناء رفع الصور.');
    }
}

function displayResults(data) {
    document.getElementById('stagingArea').classList.add('hidden');
    document.getElementById('progressContainer').classList.add('hidden');
    
    const resultsCard = document.getElementById('resultsCard');
    resultsCard.classList.remove('hidden');

    document.getElementById('resTotal').textContent = data.total_count;
    document.getElementById('resMatched').textContent = data.matched_count;
    document.getElementById('resUnmatched').textContent = data.unmatched_count;

    const tbody = document.getElementById('resultsTableBody');
    tbody.innerHTML = '';

    data.details.forEach(item => {
        const tr = document.createElement('tr');
        tr.className = item.matched ? 'hover:bg-emerald-50/40' : 'hover:bg-amber-50/40 bg-amber-50/20';

        tr.innerHTML = `
            <td class="p-2.5 text-center">
                <a href="${item.url}" target="_blank">
                    <img src="${item.url}" class="w-10 h-10 object-cover rounded border inline-block hover:scale-110 transition shadow-sm">
                </a>
            </td>
            <td class="p-2.5 font-mono font-medium text-gray-700">${item.filename}</td>
            <td class="p-2.5 font-mono font-bold text-gray-900">#${item.serial}</td>
            <td class="p-2.5">
                ${item.matched ? `
                    <a href="/bonds/${item.bond_id}" target="_blank" class="font-bold text-blue-600 hover:underline">
                        سند #${item.serial} ↗
                    </a>` : `
                    <span class="text-gray-400 italic">غير موجود</span>
                `}
            </td>
            <td class="p-2.5 text-gray-600">${item.receiver || '-'}</td>
            <td class="p-2.5 text-right">
                ${item.matched ? `
                    <span class="bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded text-[11px] inline-flex items-center gap-1">
                        <span>✓</span><span>تم الربط</span>
                    </span>` : `
                    <span class="bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded text-[11px] inline-flex items-center gap-1" title="${item.message}">
                        <span>!</span><span>غير مطابق</span>
                    </span>
                `}
            </td>
        `;
        tbody.appendChild(tr);
    });

    resultsCard.scrollIntoView({ behavior: 'smooth' });
}

function resetUploader() {
    clearStaging();
    document.getElementById('resultsCard').classList.add('hidden');
    document.getElementById('progressBar').style.width = '0%';
    document.getElementById('progressPercent').textContent = '0%';
}
</script>
@endsection
