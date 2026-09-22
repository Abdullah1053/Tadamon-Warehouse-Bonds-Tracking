@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto pb-12" id="normalizationApp">
    {{-- Page Header --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-blue-600 text-white rounded-xl shadow-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Receiver Normalization & Deduplication</h2>
                    <p class="text-sm text-gray-500 font-medium">Group and unify similar or mistyped receiver names in bonds (<code class="text-xs bg-gray-200 px-1 py-0.5 rounded text-gray-700">bonds.received_from</code>).</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="refreshClusters()" id="refreshBtn" class="flex items-center gap-2 px-4 py-2 bg-white hover:bg-gray-50 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 shadow-sm transition">
                <svg id="refreshIcon" class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Re-scan Names</span>
            </button>
        </div>
    </div>

    {{-- Stats Overview Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        {{-- Total Unique Names --}}
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Unique Names</p>
                <h3 id="statTotalNames" class="text-3xl font-black text-gray-800 mt-1">{{ number_format($stats['total_unique_names']) }}</h3>
                <p class="text-xs text-gray-500 mt-1">Distinct entries in database</p>
            </div>
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>

        {{-- Duplicate Clusters Found --}}
        <div class="bg-white rounded-xl p-5 border border-amber-200 shadow-sm flex items-center justify-between bg-gradient-to-br from-white to-amber-50/40">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-600">Similar Name Groups</p>
                <h3 id="statClustersCount" class="text-3xl font-black text-amber-600 mt-1">{{ number_format($stats['clusters_count']) }}</h3>
                <p class="text-xs text-amber-700/70 mt-1">Groups requiring review</p>
            </div>
            <div class="p-3 bg-amber-100 text-amber-700 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>

        {{-- Affected Bonds Count --}}
        <div class="bg-white rounded-xl p-5 border border-purple-200 shadow-sm flex items-center justify-between bg-gradient-to-br from-white to-purple-50/40">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-purple-600">Bonds in Duplicate Groups</p>
                <h3 id="statAffectedBonds" class="text-3xl font-black text-purple-600 mt-1">{{ number_format($stats['affected_bonds']) }}</h3>
                <p class="text-xs text-purple-700/70 mt-1">Bonds ready to be unified</p>
            </div>
            <div class="p-3 bg-purple-100 text-purple-700 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Notification Toast Container --}}
    <div id="toast" class="hidden fixed bottom-6 right-6 z-50 bg-gray-900 text-white px-5 py-3.5 rounded-xl shadow-2xl flex items-center gap-3 border border-gray-700 text-sm transform transition duration-300">
        <span id="toastIcon" class="text-green-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </span>
        <span id="toastMsg" class="font-medium">Message</span>
    </div>

    {{-- Tabs Navigation --}}
    <div class="border-b border-gray-200 mb-6 flex gap-6">
        <button onclick="switchTab('auto')" id="tabBtnAuto" class="pb-3 px-2 border-b-2 border-blue-600 text-blue-600 font-bold text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <span>Auto-Detected Groups (<span id="autoGroupsBadge">{{ count($clusters) }}</span>)</span>
        </button>
        <button onclick="switchTab('manual')" id="tabBtnManual" class="pb-3 px-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span>Manual Multi-Merge</span>
        </button>
    </div>

    {{-- TAB 1: AUTO-DETECTED CLUSTERS --}}
    <div id="tabContentAuto">
        {{-- Toolbar: Filters and Thresholds --}}
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
            {{-- Search Filter --}}
            <div class="relative w-full md:w-96">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input type="text" id="clusterSearch" placeholder="Search groups or names (e.g. البدر، راشد)..." 
                       value="{{ $search }}"
                       oninput="debounceSearch()"
                       class="w-full pl-9 pr-9 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                <button type="button" onclick="clearSearch()" id="clearSearchBtn" class="{{ empty($search) ? 'hidden' : '' }} absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Sensitivity / Threshold Controls --}}
            <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Similarity:</span>
                <div class="inline-flex rounded-lg border border-gray-300 bg-gray-50 p-0.5 text-xs font-semibold">
                    <button type="button" onclick="setThreshold(70)" class="threshold-btn px-3 py-1.5 rounded-md transition {{ $threshold == 70 ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900' }}" data-val="70">
                        70% (Broad)
                    </button>
                    <button type="button" onclick="setThreshold(80)" class="threshold-btn px-3 py-1.5 rounded-md transition {{ $threshold == 80 ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900' }}" data-val="80">
                        80% (Balanced)
                    </button>
                    <button type="button" onclick="setThreshold(85)" class="threshold-btn px-3 py-1.5 rounded-md transition {{ $threshold == 85 ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900' }}" data-val="85">
                        85% (Strict)
                    </button>
                    <button type="button" onclick="setThreshold(90)" class="threshold-btn px-3 py-1.5 rounded-md transition {{ $threshold == 90 ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900' }}" data-val="90">
                        90% (Exact/Typos)
                    </button>
                </div>
            </div>
        </div>

        {{-- Loading Spinner State --}}
        <div id="loadingState" class="hidden py-16 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent"></div>
            <p class="text-sm font-semibold text-gray-500 mt-3">Analyzing and clustering receiver names...</p>
        </div>

        {{-- Empty State (No clusters found) --}}
        <div id="emptyState" class="{{ count($clusters) > 0 ? 'hidden' : '' }} py-16 text-center bg-white rounded-2xl border border-gray-200 shadow-sm">
            <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800">All Receiver Names Are Clean!</h3>
            <p class="text-sm text-gray-500 max-w-md mx-auto mt-1">
                No duplicate variations found at the current similarity threshold. You can lower the sensitivity (e.g. to 70%) or use the Manual Multi-Merge tab to combine specific names.
            </p>
        </div>

        {{-- Clusters List Container --}}
        <div id="clustersContainer" class="space-y-6">
            @foreach($clusters as $cluster)
                <div class="cluster-card bg-white rounded-2xl border border-gray-200 shadow-sm hover:border-blue-300 transition duration-200 overflow-hidden" id="cluster-{{ $cluster['id'] }}">
                    {{-- Cluster Card Header --}}
                    <div class="bg-gray-50/80 px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-blue-100 text-blue-700 font-bold text-xs">
                                {{ $loop->iteration }}
                            </span>
                            <div>
                                <h4 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                    <span>Group:</span>
                                    <span class="text-blue-700 underline underline-offset-2">{{ $cluster['canonical_suggestion'] }}</span>
                                </h4>
                                <p class="text-xs text-gray-500">Contains {{ $cluster['variations_count'] }} spelling variations across {{ $cluster['total_bonds'] }} bonds</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="button" onclick="toggleSelectAll('{{ $cluster['id'] }}')" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                                Toggle All
                            </button>
                            <span class="bg-purple-100 text-purple-700 text-xs font-extrabold px-2.5 py-1 rounded-full">
                                {{ $cluster['total_bonds'] }} Bonds Total
                            </span>
                        </div>
                    </div>

                    {{-- Variations Table/List --}}
                    <div class="p-6">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Detected Variations in Database:</p>
                        
                        <div class="space-y-2 mb-6">
                            @foreach($cluster['variations'] as $variant)
                                <label class="variant-item flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:bg-blue-50/40 hover:border-blue-200 transition cursor-pointer group">
                                    <div class="flex items-center gap-3">
                                        {{-- Checkbox to include/exclude this variation --}}
                                        <input type="checkbox" 
                                               name="variants_{{ $cluster['id'] }}[]" 
                                               value="{{ $variant['name'] }}" 
                                               checked
                                               class="variant-checkbox w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer"
                                               onchange="updateGroupBondCount('{{ $cluster['id'] }}')">

                                        {{-- Radio button to pick as master --}}
                                        <input type="radio" 
                                               name="master_{{ $cluster['id'] }}" 
                                               value="{{ $variant['name'] }}" 
                                               {{ $variant['name'] === $cluster['canonical_suggestion'] ? 'checked' : '' }}
                                               class="master-radio w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500 cursor-pointer"
                                               onchange="setMasterName('{{ $cluster['id'] }}', '{{ addslashes($variant['name']) }}')"
                                               title="Set as master canonical name">

                                        <span class="text-sm font-bold text-gray-800 group-hover:text-blue-900" dir="auto">
                                            {{ $variant['name'] }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <button type="button" 
                                                onclick="previewVariantBonds('{{ addslashes($variant['name']) }}')" 
                                                class="text-xs text-gray-400 hover:text-blue-600 underline transition">
                                            Preview
                                        </button>
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $variant['name'] === $cluster['canonical_suggestion'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $variant['count'] }} {{ $variant['count'] == 1 ? 'bond' : 'bonds' }}
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        {{-- Canonical Target Field & Merge Button --}}
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 flex flex-col md:flex-row items-center justify-between gap-4">
                            <div class="w-full md:flex-1">
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                                    Target Canonical Name (The name that all checked variants will become):
                                </label>
                                <div class="relative">
                                    <input type="text" 
                                           id="target-{{ $cluster['id'] }}" 
                                           value="{{ $cluster['canonical_suggestion'] }}" 
                                           dir="auto"
                                           class="w-full px-3.5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                                </div>
                            </div>

                            <div class="w-full md:w-auto flex items-end">
                                <button type="button" 
                                        onclick="executeMerge('{{ $cluster['id'] }}')" 
                                        id="merge-btn-{{ $cluster['id'] }}"
                                        class="w-full md:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow-sm hover:shadow transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Merge All Into Target</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- TAB 2: MANUAL MULTI-MERGE --}}
    <div id="tabContentManual" class="hidden">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-900">Custom Manual Merger</h3>
                <p class="text-sm text-gray-500">Pick any two or more names from the database, specify the target master name, and merge them immediately.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Left: Names Picker --}}
                <div>
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                            1. Select Names to Merge:
                        </label>
                        <input type="text" id="manualSearchInput" oninput="filterManualList()" placeholder="Filter names..." 
                               class="w-full px-3.5 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div class="border border-gray-200 rounded-xl max-h-96 overflow-y-auto p-2 space-y-1 bg-gray-50/50" id="manualNamesList">
                        @foreach($allReceivers as $recName => $recCount)
                            <label class="manual-name-item flex items-center justify-between p-2 rounded-lg hover:bg-blue-50/50 cursor-pointer text-sm">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" 
                                           value="{{ $recName }}" 
                                           class="manual-checkbox w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                           onchange="handleManualSelectionChange()">
                                    <span class="manual-name-text font-medium text-gray-800" dir="auto">{{ $recName }}</span>
                                </div>
                                <span class="text-xs bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full font-semibold">{{ $recCount }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Selected: <span id="manualSelectedCount" class="font-bold text-blue-600">0</span> names</p>
                </div>

                {{-- Right: Target & Action --}}
                <div class="flex flex-col justify-between bg-gray-50 p-6 rounded-xl border border-gray-200">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">
                            2. Target Canonical Name:
                        </label>
                        <input type="text" id="manualTargetName" placeholder="Enter target master name..." dir="auto"
                               class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-900 focus:ring-2 focus:ring-blue-500 outline-none mb-4">
                        
                        <div class="text-xs text-gray-500 space-y-1 mb-6">
                            <p>• All selected names will be updated to this exact name in <code class="bg-gray-200 px-1 py-0.5 rounded">bonds.received_from</code>.</p>
                            <p>• You can click on any selected name below to quickly set it as the target name.</p>
                        </div>

                        <div id="manualSelectedChips" class="flex flex-wrap gap-2 mb-4">
                            {{-- Dynamically populated chips --}}
                        </div>
                    </div>

                    <div>
                        <button type="button" onclick="executeManualMerge()" id="manualMergeBtn" 
                                class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                            <span>Merge Selected Names</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PREVIEW BONDS MODAL --}}
    <div id="previewModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-gray-200 overflow-hidden transform transition duration-300">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Bonds Preview</h3>
                    <p class="text-xs text-gray-500">Sample bonds matching: <strong id="previewVariantTitle" class="text-blue-600"></strong></p>
                </div>
                <button type="button" onclick="closePreviewModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6 max-h-96 overflow-y-auto">
                <div id="previewBondsList" class="space-y-2">
                    {{-- Populated via JS --}}
                </div>
            </div>
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="closePreviewModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-semibold rounded-lg transition">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentThreshold = {{ $threshold }};
let searchDebounceTimeout = null;

// Switch main tabs
function switchTab(tab) {
    if (tab === 'auto') {
        document.getElementById('tabContentAuto').classList.remove('hidden');
        document.getElementById('tabContentManual').classList.add('hidden');
        document.getElementById('tabBtnAuto').className = 'pb-3 px-2 border-b-2 border-blue-600 text-blue-600 font-bold text-sm flex items-center gap-2';
        document.getElementById('tabBtnManual').className = 'pb-3 px-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm flex items-center gap-2';
    } else {
        document.getElementById('tabContentAuto').classList.add('hidden');
        document.getElementById('tabContentManual').classList.remove('hidden');
        document.getElementById('tabBtnManual').className = 'pb-3 px-2 border-b-2 border-blue-600 text-blue-600 font-bold text-sm flex items-center gap-2';
        document.getElementById('tabBtnAuto').className = 'pb-3 px-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm flex items-center gap-2';
    }
}

// Set similarity threshold
function setThreshold(val) {
    currentThreshold = val;
    document.querySelectorAll('.threshold-btn').forEach(btn => {
        if (parseInt(btn.getAttribute('data-val')) === val) {
            btn.className = 'threshold-btn px-3 py-1.5 rounded-md transition bg-white text-blue-600 shadow-sm font-bold';
        } else {
            btn.className = 'threshold-btn px-3 py-1.5 rounded-md transition text-gray-600 hover:text-gray-900';
        }
    });
    fetchClusters();
}

// Search debounce
function debounceSearch() {
    clearTimeout(searchDebounceTimeout);
    const searchVal = document.getElementById('clusterSearch').value.trim();
    const clearBtn = document.getElementById('clearSearchBtn');
    if (searchVal.length > 0) {
        clearBtn.classList.remove('hidden');
    } else {
        clearBtn.classList.add('hidden');
    }
    searchDebounceTimeout = setTimeout(fetchClusters, 300);
}

function clearSearch() {
    document.getElementById('clusterSearch').value = '';
    document.getElementById('clearSearchBtn').classList.add('hidden');
    fetchClusters();
}

function refreshClusters() {
    const icon = document.getElementById('refreshIcon');
    icon.classList.add('animate-spin');
    fetchClusters().finally(() => {
        setTimeout(() => icon.classList.remove('animate-spin'), 600);
    });
}

// Fetch clusters dynamically via API
async function fetchClusters() {
    const query = document.getElementById('clusterSearch').value.trim();
    const loading = document.getElementById('loadingState');
    const container = document.getElementById('clustersContainer');
    const emptyState = document.getElementById('emptyState');

    loading.classList.remove('hidden');
    container.classList.add('opacity-40');

    try {
        const url = new URL('{{ route("receivers.api.clusters") }}', window.location.origin);
        url.searchParams.set('threshold', currentThreshold);
        if (query) url.searchParams.set('search', query);

        const response = await fetch(url.toString(), {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();

        // Update stats
        document.getElementById('statTotalNames').innerText = Number(data.stats.total_unique_names).toLocaleString();
        document.getElementById('statClustersCount').innerText = Number(data.stats.clusters_count).toLocaleString();
        document.getElementById('statAffectedBonds').innerText = Number(data.stats.affected_bonds).toLocaleString();
        document.getElementById('autoGroupsBadge').innerText = data.clusters.length;

        // Render clusters
        renderClusters(data.clusters);
    } catch (err) {
        console.error('Error loading clusters:', err);
        showToast('Error loading clusters', 'error');
    } finally {
        loading.classList.add('hidden');
        container.classList.remove('opacity-40');
    }
}

// Client-side render of cluster cards
function renderClusters(clusters) {
    const container = document.getElementById('clustersContainer');
    const emptyState = document.getElementById('emptyState');

    if (!clusters || clusters.length === 0) {
        container.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }

    emptyState.classList.add('hidden');

    let html = '';
    clusters.forEach((cluster, idx) => {
        html += `
        <div class="cluster-card bg-white rounded-2xl border border-gray-200 shadow-sm hover:border-blue-300 transition duration-200 overflow-hidden" id="cluster-${cluster.id}">
            <div class="bg-gray-50/80 px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-blue-100 text-blue-700 font-bold text-xs">
                        ${idx + 1}
                    </span>
                    <div>
                        <h4 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <span>Group:</span>
                            <span class="text-blue-700 underline underline-offset-2">${escapeHtml(cluster.canonical_suggestion)}</span>
                        </h4>
                        <p class="text-xs text-gray-500">Contains ${cluster.variations_count} spelling variations across ${cluster.total_bonds} bonds</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="toggleSelectAll('${cluster.id}')" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                        Toggle All
                    </button>
                    <span class="bg-purple-100 text-purple-700 text-xs font-extrabold px-2.5 py-1 rounded-full">
                        ${cluster.total_bonds} Bonds Total
                    </span>
                </div>
            </div>

            <div class="p-6">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Detected Variations in Database:</p>
                <div class="space-y-2 mb-6">
                    ${cluster.variations.map(v => `
                        <label class="variant-item flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:bg-blue-50/40 hover:border-blue-200 transition cursor-pointer group">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" 
                                       name="variants_${cluster.id}[]" 
                                       value="${escapeHtml(v.name)}" 
                                       checked
                                       class="variant-checkbox w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer"
                                       onchange="updateGroupBondCount('${cluster.id}')">

                                <input type="radio" 
                                       name="master_${cluster.id}" 
                                       value="${escapeHtml(v.name)}" 
                                       ${v.name === cluster.canonical_suggestion ? 'checked' : ''}
                                       class="master-radio w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500 cursor-pointer"
                                       onchange="setMasterName('${cluster.id}', '${escapeHtml(v.name)}')"
                                       title="Set as master canonical name">

                                <span class="text-sm font-bold text-gray-800 group-hover:text-blue-900" dir="auto">
                                    ${escapeHtml(v.name)}
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" 
                                        onclick="previewVariantBonds('${escapeHtml(v.name)}')" 
                                        class="text-xs text-gray-400 hover:text-blue-600 underline transition">
                                    Preview
                                </button>
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full ${v.name === cluster.canonical_suggestion ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}">
                                    ${v.count} ${v.count === 1 ? 'bond' : 'bonds'}
                                </span>
                            </div>
                        </label>
                    `).join('')}
                </div>

                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="w-full md:flex-1">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">
                            Target Canonical Name:
                        </label>
                        <input type="text" 
                               id="target-${cluster.id}" 
                               value="${escapeHtml(cluster.canonical_suggestion)}" 
                               dir="auto"
                               class="w-full px-3.5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-900 focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>

                    <div class="w-full md:w-auto flex items-end">
                        <button type="button" 
                                onclick="executeMerge('${cluster.id}')" 
                                id="merge-btn-${cluster.id}"
                                class="w-full md:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow-sm hover:shadow transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Merge All Into Target</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
    });

    container.innerHTML = html;
}

// Helpers for cluster interactions
function toggleSelectAll(clusterId) {
    const checkboxes = document.querySelectorAll(`input[name="variants_${clusterId}[]"]`);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
}

function setMasterName(clusterId, name) {
    const input = document.getElementById(`target-${clusterId}`);
    if (input) input.value = name;
}

// Execute Merge for a cluster
async function executeMerge(clusterId) {
    const targetInput = document.getElementById(`target-${clusterId}`);
    const targetName = targetInput ? targetInput.value.trim() : '';

    if (!targetName) {
        alert('Please enter or select a valid target canonical name.');
        return;
    }

    const checkedBoxes = document.querySelectorAll(`input[name="variants_${clusterId}[]"]:checked`);
    const variants = Array.from(checkedBoxes).map(cb => cb.value);

    if (variants.length === 0) {
        alert('Please select at least one variation to merge.');
        return;
    }

    const confirmMsg = `Are you sure you want to merge ${variants.length} variations into:\n"${targetName}"?\n\nThis will update all corresponding bonds in the database.`;
    if (!confirm(confirmMsg)) return;

    const btn = document.getElementById(`merge-btn-${clusterId}`);
    btn.disabled = true;
    btn.innerHTML = `
        <span class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
        <span>Merging...</span>
    `;

    try {
        const response = await fetch('{{ route("receivers.merge") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                target_name: targetName,
                variants: variants
            })
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');

            // Animate card removal
            const card = document.getElementById(`cluster-${clusterId}`);
            if (card) {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '0';
                card.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    card.remove();
                    // Re-calculate stats or re-fetch
                    fetchClusters();
                }, 500);
            }
        } else {
            showToast(data.message || 'Error occurred during merge', 'error');
            btn.disabled = false;
            btn.innerText = 'Merge All Into Target';
        }
    } catch (err) {
        console.error(err);
        showToast('Server error while merging records.', 'error');
        btn.disabled = false;
        btn.innerText = 'Merge All Into Target';
    }
}

// Manual Tab: Handle selection changes
function filterManualList() {
    const q = document.getElementById('manualSearchInput').value.toLowerCase().trim();
    document.querySelectorAll('.manual-name-item').forEach(item => {
        const text = item.querySelector('.manual-name-text').innerText.toLowerCase();
        if (text.includes(q)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function handleManualSelectionChange() {
    const selectedCbs = document.querySelectorAll('.manual-checkbox:checked');
    const count = selectedCbs.length;
    document.getElementById('manualSelectedCount').innerText = count;

    const chipsContainer = document.getElementById('manualSelectedChips');
    chipsContainer.innerHTML = '';

    const targetInput = document.getElementById('manualTargetName');

    selectedCbs.forEach((cb, idx) => {
        const val = cb.value;
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold px-2.5 py-1 rounded-full transition flex items-center gap-1';
        chip.innerHTML = `<span>${escapeHtml(val)}</span>`;
        chip.title = 'Click to use as master target name';
        chip.onclick = () => {
            targetInput.value = val;
        };
        chipsContainer.appendChild(chip);

        // Auto-fill target name with first selected if target is empty
        if (idx === 0 && !targetInput.value.trim()) {
            targetInput.value = val;
        }
    });
}

async function executeManualMerge() {
    const targetName = document.getElementById('manualTargetName').value.trim();
    if (!targetName) {
        alert('Please enter or select a target canonical name.');
        return;
    }

    const selectedCbs = document.querySelectorAll('.manual-checkbox:checked');
    const selectedNames = Array.from(selectedCbs).map(cb => cb.value);

    if (selectedNames.length === 0) {
        alert('Please select at least one name to merge.');
        return;
    }

    if (!confirm(`Merge ${selectedNames.length} selected names into:\n"${targetName}"?`)) {
        return;
    }

    const btn = document.getElementById('manualMergeBtn');
    btn.disabled = true;
    btn.innerText = 'Merging...';

    try {
        const response = await fetch('{{ route("receivers.mergeCustom") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                target_name: targetName,
                selected_names: selectedNames
            })
        });

        const data = await response.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showToast('Error during merge', 'error');
            btn.disabled = false;
            btn.innerText = 'Merge Selected Names';
        }
    } catch (err) {
        console.error(err);
        showToast('Server communication error', 'error');
        btn.disabled = false;
        btn.innerText = 'Merge Selected Names';
    }
}

// Preview Modal
async function previewVariantBonds(variantName) {
    document.getElementById('previewVariantTitle').innerText = variantName;
    const modal = document.getElementById('previewModal');
    const list = document.getElementById('previewBondsList');
    list.innerHTML = '<div class="py-8 text-center text-sm text-gray-500 animate-pulse">Loading sample bonds...</div>';
    modal.classList.remove('hidden');

    try {
        const response = await fetch(`{{ route("receivers.api.preview") }}?variants[]=${encodeURIComponent(variantName)}`);
        const data = await response.json();

        if (data.bonds.length === 0) {
            list.innerHTML = '<p class="text-sm text-gray-500 py-4 text-center">No bonds found for this name.</p>';
            return;
        }

        let html = '';
        data.bonds.forEach(bond => {
            html += `
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-sm flex justify-between items-center">
                <div>
                    <span class="font-bold text-gray-800">Bond #${bond.bond_serial || bond.id}</span>
                    <span class="text-xs text-gray-500 ml-2">${bond.date || 'No date'}</span>
                    ${bond.operation_name ? `<span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded ml-2 font-medium">${escapeHtml(bond.operation_name)}</span>` : ''}
                </div>
                <div class="text-xs text-gray-600 truncate max-w-xs" title="${escapeHtml(bond.note || '')}">
                    ${escapeHtml(bond.note || 'No notes')}
                </div>
            </div>
            `;
        });

        if (data.total > data.bonds.length) {
            html += `<p class="text-xs text-center text-gray-400 mt-2">Showing 25 of ${data.total} total matching bonds</p>`;
        }

        list.innerHTML = html;
    } catch (e) {
        list.innerHTML = '<p class="text-sm text-red-500 py-4 text-center">Error loading bonds preview.</p>';
    }
}

function closePreviewModal() {
    document.getElementById('previewModal').classList.add('hidden');
}

// Toast notification helper
function showToast(msg, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toastMsg');
    const toastIcon = document.getElementById('toastIcon');

    toastMsg.innerText = msg;
    if (type === 'success') {
        toastIcon.className = 'text-green-400';
    } else {
        toastIcon.className = 'text-red-400';
    }

    toast.classList.remove('hidden');
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 4500);
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>
@endsection
