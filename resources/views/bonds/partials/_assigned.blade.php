<div class="bg-white rounded-lg shadow border-t-4 border-green-600">
    <div class="p-4 border-b bg-gray-50">
        <h3 class="font-bold text-gray-700 uppercase text-sm">✅ Assigned Bonds (Grouped)</h3>
    </div>
    <table class="w-full text-left">
        <thead class="bg-gray-100 text-xs text-gray-500">
            <tr>
                <th class="p-3">Serial</th>
                <th class="p-3">Stack Name</th>
                <th class="p-3">Receiver</th>
                <th class="p-3">Link</th>
                <th class="p-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="text-sm">
            @foreach ($assignedBonds as $bond)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-bold {{ $bond->is_missing ? 'text-red-600' : 'text-blue-600' }}">
                        #{{ $bond->bond_serial }}
                        @if ($bond->is_missing)
                            <span class="text-[10px] uppercase">[Missing]</span>
                        @endif
                    </td>
                    <td class="p-3"><span
                            class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-bold">{{ $bond->stack->stack_name }}</span>
                    </td>
                    <td class="p-3 text-gray-600">{{ $bond->received_from }}</td>
                    <td class="p-3">
                        @if ($bond->bond_link)
                            <a href="{{ $bond->bond_link }}" target="_blank" class="text-blue-600 hover:underline text-xs font-semibold inline-flex items-center gap-1">🔗 Link</a>
                        @else
                            <span class="text-gray-300 text-xs">-</span>
                        @endif
                    </td>
                    <td class="p-3 text-right flex justify-end gap-2">
                        <a href="{{ route('bonds.show', $bond->id) }}" class="text-blue-600 text-xs font-bold">View</a>
                        <a href="{{ route('bonds.edit', $bond->id) }}"
                            class="text-orange-600 text-xs font-bold">Edit</a>

                        <form action="{{ route('bonds.detach', $bond->id) }}" method="POST">
                            @csrf <button class="text-orange-500 hover:underline text-xs">Unstack</button>
                        </form>
                        <form action="{{ route('bonds.destroy', $bond->id) }}" method="POST"
                            onsubmit="return confirm('Delete this record permanently?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline text-xs font-bold">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
