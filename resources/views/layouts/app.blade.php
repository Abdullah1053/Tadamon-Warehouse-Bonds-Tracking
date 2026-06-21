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
                <a href="{{ route('dashboard') }}" class="hover:text-blue-200 transition">Stacking Dashboard</a>
                <a href="{{ route('bonds.index') }}" class="hover:text-blue-200 transition">Bonds Archive</a>
            </div>
        </div>
        <div>
            <a href="{{ route('bonds.create') }}" class="bg-white text-blue-600 px-4 py-2 rounded shadow font-bold hover:bg-blue-50 transition">
                + New Paper Bond
            </a>
        </div>
    </div>
</nav>
    <div class="container mx-auto px-4">
        @yield('content')
    </div>
</body>
</html>