<div class="table-responsive" id="bonds-table-wrapper">
    <table class="table table-hover border" id="bondsTable">
        <thead class="bg-light">
            <tr>
                <th width="40"><input type="checkbox" id="selectAll"></th>
                <th>Bond ID</th>
                <th>Voucher Reference</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bonds as $bond)
                <tr id="bond-row-{{ $bond->id }}">
                    <td>
                        <input type="checkbox" class="bond-checkbox" value="{{ $bond->id }}">
                    </td>
                    <td><strong>#{{ $bond->id }}</strong></td>
                    <td>{{ $bond->voucher_no }}</td>
                    <td>{{ number_format($bond->amount, 2) }}</td>
                    <td>
                        <span class="badge bg-{{ $bond->stack_id ? 'success' : 'secondary' }}">
                            {{ $bond->stack_id ? 'Assigned' : 'Pending' }}
                        </span>
                    </td>
                    <td>{{ $bond->created_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No bonds found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>