@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Material Bond Archive</h2>
        <p class="text-gray-500">Manage unassigned delivery notes and review grouped history.</p>
    </div>

    {{-- 1. Unassigned Table (Partial) --}}
    @include('bonds.partials._unassigned')

    {{-- 2. Assigned Table (Partial) --}}
    @include('bonds.partials._assigned')
<script>
// Toggle all checkboxes
document.getElementById('selectAll').onclick = (e) => {
    document.querySelectorAll('.bond-checkbox').forEach(cb => cb.checked = e.target.checked);
};

async function assignSelectedBonds() {
    const selectedCheckboxes = document.querySelectorAll('.bond-checkbox:checked');
    const ids = Array.from(selectedCheckboxes).map(cb => cb.value);
    const stackId = document.getElementById('target_stack').value;
    const stackName = document.getElementById('target_stack').options[document.getElementById('target_stack').selectedIndex].text;

    if (ids.length === 0) return alert('Please select at least one bond.');
    if (!stackId) return alert('Please select a target stack.');

    if (!confirm(`Assign ${ids.length} bonds to "${stackName}"?`)) return;

    try {
        const response = await fetch('{{ route("bonds.bulkAssign") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ bond_ids: ids, stack_id: stackId })
        });

        const result = await response.json();
        if (result.success) {
            alert(result.message);
            // Update the UI without reloading
            ids.forEach(id => {
                const cell = document.getElementById(`stack-cell-${id}`);
                cell.innerHTML = `<span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-bold">${stackName}</span>`;
                document.getElementById(`bond-row-${id}`).classList.add('bg-green-50');
            });
            // Reset selection
            document.getElementById('selectAll').checked = false;
            selectedCheckboxes.forEach(cb => cb.checked = false);
        }
    } catch (error) {
        alert('Error performing bulk assignment.');
        console.error(error);
    }
}
</script>
@endsection