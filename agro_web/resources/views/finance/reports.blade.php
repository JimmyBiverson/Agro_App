@extends('layouts.app')
@section('title', 'Financial Reports')
@section('page-title', 'Financial Reports')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card-stat text-center">
        <p class="text-2xl font-bold text-emerald-500">UGX {{ number_format($data['total_collected_month']) }}</p>
        <p class="text-xs" style="color:var(--text-muted)">Collected This Month</p>
    </div>
    <div class="card-stat text-center">
        <p class="text-2xl font-bold" style="color:var(--text-primary)">UGX {{ number_format($data['total_collected_ytd']) }}</p>
        <p class="text-xs" style="color:var(--text-muted)">Collected Year-to-Date</p>
    </div>
    <div class="card-stat text-center">
        <p class="text-2xl font-bold text-red-500">UGX {{ number_format($data['total_outstanding']) }}</p>
        <p class="text-xs" style="color:var(--text-muted)">Total Outstanding</p>
    </div>
</div>

<div class="card-full">
    <div class="card-header">
        <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Monthly Collections ({{ now()->year }})</h3>
    </div>
    <div class="card-body">
        @php
            $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            $collectionMap = $data['monthly_collections']->pluck('total', 'month')->toArray();
            $maxVal = max(array_values($collectionMap) ?: [1]);
        @endphp
        <div class="space-y-3">
            @foreach($months as $i => $month)
            @php $val = $collectionMap[$i + 1] ?? 0; @endphp
            <div class="flex items-center gap-3">
                <span class="text-xs w-8" style="color:var(--text-muted)">{{ $month }}</span>
                <div class="flex-1 h-6 rounded-full overflow-hidden" style="background:var(--bg-card)">
                    <div class="h-full rounded-full gradient-indigo transition-all duration-500" style="width: {{ $maxVal > 0 ? ($val / $maxVal) * 100 : 0 }}%"></div>
                </div>
                <span class="text-xs font-medium w-24 text-right" style="color:var(--text-primary)">UGX {{ number_format($val) }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
