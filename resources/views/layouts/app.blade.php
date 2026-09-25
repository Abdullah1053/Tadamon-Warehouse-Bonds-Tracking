<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Warehouse Bond System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
  <nav class="bg-blue-600 p-4 text-white shadow-lg mb-8">
    <div class="container mx-auto flex justify-between items-center">
        <div class="flex items-center gap-8">
            <h1 class="font-bold text-xl tracking-tight">WAREHOUSE PRO</h1>
            <div class="flex gap-4 text-sm font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-200 transition {{ request()->routeIs('dashboard', 'stacks.*') ? 'text-white font-semibold underline underline-offset-4' : 'text-blue-100' }}">Stacking Dashboard</a>
                <a href="{{ route('bonds.index') }}" class="hover:text-blue-200 transition {{ request()->routeIs('bonds.index') ? 'text-white font-semibold underline underline-offset-4' : 'text-blue-100' }}">Bonds Archive</a>
                <a href="{{ route('bonds.uploadImagesView') }}" class="hover:text-blue-200 transition {{ request()->routeIs('bonds.uploadImagesView') ? 'text-white font-semibold underline underline-offset-4' : 'text-blue-100' }}">Upload Images</a>
                <a href="{{ route('receivers.normalize') }}" class="hover:text-blue-200 transition {{ request()->routeIs('receivers.*') ? 'text-white font-semibold underline underline-offset-4' : 'text-blue-100' }}">Normalize Receivers</a>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('bonds.create', ['type' => 'receipt']) }}" class="inline-flex items-center gap-1.5 bg-blue-700 hover:bg-blue-800 text-white px-3.5 py-2 rounded-lg shadow-sm text-xs font-bold transition border border-blue-500">
                <span>📥</span>
                <span>+ سند استلام مواد</span>
            </a>
            <a href="{{ route('bonds.createDisbursement') }}" class="inline-flex items-center gap-1.5 bg-purple-600 hover:bg-purple-700 text-white px-3.5 py-2 rounded-lg shadow-sm text-xs font-bold transition border border-purple-400">
                <span>📤</span>
                <span>+ سند صرف مواد</span>
            </a>
        </div>
    </div>
</nav>
    <div class="container mx-auto px-4">
        @yield('content')
    </div>
</body>
</html>