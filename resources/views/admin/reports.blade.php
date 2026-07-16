@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports & Analytics')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <a href="{{ route('web.admin.reports') }}" class="card-full transition" onmouseover="this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.boxShadow=''">
        <div class="p-6">
            <div class="h-10 w-10 rounded-lg gradient-indigo flex items-center justify-center mb-3">
                <i class="fas fa-chart-bar text-white text-sm"></i>
            </div>
            <h3 class="font-semibold" style="color:var(--text-primary)">Sales Reports</h3>
            <p class="text-sm mt-1" style="color:var(--text-muted)">Sales by franchise, category, date range with trends</p>
        </div>
    </a>
    <div class="card-full transition" onmouseover="this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.boxShadow=''">
        <div class="p-6">
            <div class="h-10 w-10 rounded-lg gradient-green flex items-center justify-center mb-3">
                <i class="fas fa-money-bill-wave text-white text-sm"></i>
            </div>
            <h3 class="font-semibold" style="color:var(--text-primary)">Payment Reports</h3>
            <p class="text-sm mt-1" style="color:var(--text-muted)">Payment verification and reconciliation status</p>
        </div>
    </div>
    <div class="card-full transition" onmouseover="this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.boxShadow=''">
        <div class="p-6">
            <div class="h-10 w-10 rounded-lg gradient-amber flex items-center justify-center mb-3">
                <i class="fas fa-clipboard-list text-white text-sm"></i>
            </div>
            <h3 class="font-semibold" style="color:var(--text-primary)">Order Reports</h3>
            <p class="text-sm mt-1" style="color:var(--text-muted)">Order fulfillment and processing analytics</p>
        </div>
    </div>
    <div class="card-full transition" onmouseover="this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.boxShadow=''">
        <div class="p-6">
            <div class="h-10 w-10 rounded-lg gradient-rose flex items-center justify-center mb-3">
                <i class="fas fa-box-open text-white text-sm"></i>
            </div>
            <h3 class="font-semibold" style="color:var(--text-primary)">Inventory Reports</h3>
            <p class="text-sm mt-1" style="color:var(--text-muted)">Warehouse and franchise stock levels</p>
        </div>
    </div>
    <div class="card-full transition" onmouseover="this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.boxShadow=''">
        <div class="p-6">
            <div class="h-10 w-10 rounded-lg gradient-cyan flex items-center justify-center mb-3">
                <i class="fas fa-chart-pie text-white text-sm"></i>
            </div>
            <h3 class="font-semibold" style="color:var(--text-primary)">Franchise Comparison</h3>
            <p class="text-sm mt-1" style="color:var(--text-muted)">Compare performance across all franchises</p>
        </div>
    </div>
</div>
@endsection
