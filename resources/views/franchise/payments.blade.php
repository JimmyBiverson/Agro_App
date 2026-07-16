@extends('layouts.app')
@section('title', 'My Payments')
@section('page-title', 'Payment History')

@section('content')
<div class="card-full">
    <div class="card-header">
        <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Payments ({{ $payments->total() }})</h3>
    </div>
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="w-full table-dark">
                <thead>
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <th class="px-4 py-3 text-left">Payment #</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-left">Method</th>
                        <th class="px-4 py-3 text-left">Reference</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left">Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                    <tr class="border-b" style="border-color:var(--border-color)">
                        <td class="px-4 py-3 text-sm font-medium" style="color:var(--indigo)">{{ $p->payment_number }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-right" style="color:var(--text-primary)">UGX {{ number_format($p->amount) }}</td>
                        <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ ucfirst(str_replace('_', ' ', $p->payment_method)) }}</td>
                        <td class="px-4 py-3 text-sm" style="color:var(--text-secondary)">{{ $p->transaction_reference ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($p->status === 'pending')
                            <span class="badge badge-warning">Pending</span>
                            @elseif($p->status === 'accepted')
                            <span class="badge badge-success">Accepted</span>
                            @else
                            <span class="badge badge-danger">{{ ucfirst($p->status) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs" style="color:var(--text-muted)">{{ $p->submitted_at?->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">No payments submitted yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="px-4 py-3" style="border-top:1px solid var(--border-color)">{{ $payments->links() }}</div>
</div>
@endsection
