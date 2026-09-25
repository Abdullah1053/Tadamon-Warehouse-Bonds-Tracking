<div class="bg-white rounded-lg shadow border-t-4 border-yellow-500 mb-8">
    <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
        <h3 class="font-bold text-gray-700 uppercase text-sm">⚠️ Unassigned Bonds (Action Required)</h3>
        <div class="flex items-center gap-2">
            <select id="target_stack" class="border p-1 rounded text-sm bg-white">
                <option value="">Choose Stack...</option>
                @foreach ($stacks as $stack)
                    <option value="{{ $stack->id }}">{{ $stack->stack_name }}</option>
                @endforeach
            </select>
            <button onclick="assignSelectedBonds()"
                class="bg-blue-600 text-white px-4 py-1 rounded text-sm font-bold hover:bg-blue-700">Assign
                Selected</button>
        </div>
    </div>
    <table class="w-full text-left">
        <thead class="bg-gray-100 text-xs text-gray-500">
            <tr>
                <th class="p-3 w-10"><input type="checkbox" id="selectAll"></th>
                <th class="p-3">Serial</th>
                <th class="p-3">Receiver</th>
                <th class="p-3">Operation</th>
                <th class="p-3">Items</th>
                <th class="p-3">Link</th>
                <th class="p-3 text-right">Actions</th>

            </tr>
        </thead>
        <tbody class="text-sm">
            @foreach ($unassignedBonds as $bond)
                <tr class="border-b hover:bg-yellow-50" id="row-{{ $bond->id }}">
                    <td class="p-3"><input type="checkbox" class="bond-checkbox" value="{{ $bond->id }}"></td>
                    <td class="p-3 font-bold {{ $bond->isMissing() ? 'text-red-600' : ($bond->isCancelled() ? 'text-amber-600' : ($bond->isDisbursement() ? 'text-purple-600' : 'text-blue-600')) }}">
                        #{{ $bond->bond_serial }}
                        @if ($bond->isDisbursement())
                            <span class="bg-purple-100 text-purple-700 border border-purple-200 px-1.5 py-0.5 rounded text-[10px] font-bold">صرف</span>
                        @endif
                        @if ($bond->isMissing())
                            <span class="bg-red-100 text-red-700 border border-red-200 px-1.5 py-0.5 rounded text-[10px] font-extrabold uppercase">مفقود</span>
                        @elseif ($bond->isCancelled())
                            <span class="bg-amber-100 text-amber-800 border border-amber-200 px-1.5 py-0.5 rounded text-[10px] font-extrabold uppercase">ملغي</span>
                        @endif
                    </td>

                    <td class="p-3">
                        @if($bond->isMissing())
                            <span class="text-red-600 font-bold">مفقود</span>
                        @elseif($bond->isCancelled())
                            <span class="text-amber-700 font-bold">ملغي</span>
                        @else
                            {{ $bond->received_from }}
                        @endif
                    </td>
                    <td class="p-3">{{ $bond->operation_name }}</td>
                    <td class="p-3 text-gray-400">
                        @if($bond->isMissing() || $bond->isCancelled())
                            <span class="text-gray-300 text-xs">-</span>
                        @else
                            {{ $bond->items->count() }} items
                        @endif
                    </td>
                    <td class="p-3">
                        @if ($bond->bond_link)
                            <a href="{{ $bond->image_url }}" target="_blank" class="text-blue-600 hover:underline text-xs font-semibold inline-flex items-center gap-1">🔗 Link</a>
                        @else
                            <span class="text-gray-300 text-xs">-</span>
                        @endif
                    </td>
                    <td class="p-3 text-right flex justify-end gap-2">
                        <a href="{{ route('bonds.show', $bond->id) }}" class="text-blue-600 text-xs font-bold">View</a>
                        <a href="{{ route('bonds.edit', $bond->id) }}"
                            class="text-orange-600 text-xs font-bold">Edit</a>
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
