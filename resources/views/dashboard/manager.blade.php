@extends('layouts.app')

@php $pageTitle = 'Dashboard'; @endphp

@section('content')
{{-- ============================================================
     DASHBOARD MANAGER — Read-only, identik operasional
     ============================================================ --}}
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard Manager</h1>
            <p class="page-subtitle">Pantau aktivitas inventaris secara keseluruhan</p>
        </div>
        {{-- No action buttons for manager --}}
        <span class="badge badge-manager">Strategic Panel</span>
    </div>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="stat-card-label">Total Unit</p>
                    <p class="stat-card-value">{{ $stats['total_unit'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $stats['total_barang'] }} jenis barang</p>
                </div>
                <div class="stat-card-icon bg-blue-50">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="stat-card-label">Unit Tersedia</p>
                    <p class="stat-card-value text-emerald-600">{{ $stats['unit_tersedia'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">siap dipinjam</p>
                </div>
                <div class="stat-card-icon bg-emerald-50">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="stat-card-label">Sedang Dipinjam</p>
                    <p class="stat-card-value text-blue-600">{{ $stats['sedang_dipinjam'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">aktif digunakan</p>
                </div>
                <div class="stat-card-icon bg-blue-50">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="stat-card-label">Bermasalah</p>
                    <p class="stat-card-value text-orange-600">{{ $stats['unit_bermasalah'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">maintenance / hilang</p>
                </div>
                <div class="stat-card-icon bg-orange-50">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- PERSETUJUAN TERTUNDA (MANAGER APPROVAL) --}}
    @if($pendingApprovals->isNotEmpty())
        <div class="card border border-amber-200 bg-amber-50/10">
            <div class="card-header border-b border-amber-100 flex justify-between items-center bg-amber-50/30">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <p class="card-title text-amber-900 font-bold">Otorisasi Peminjaman Tertunda (Memerlukan Persetujuan Manager)</p>
                </div>
                <span class="badge badge-menunggu-admin">{{ $pendingApprovals->count() }} Pengajuan</span>
            </div>
            <div class="divide-y divide-gray-100 bg-white">
                @foreach($pendingApprovals as $p)
                    <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-gray-900">{{ $p->kode_peminjaman }}</p>
                                @if($p->fifo_override)
                                    <span class="badge badge-maintenance">FIFO Override</span>
                                @else
                                    <span class="badge badge-terverifikasi">Nilai Tinggi (>10JT)</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500">Peminjam: <strong class="text-gray-700">{{ $p->borrower->name }}</strong> · Diajukan: {{ $p->tanggal_pengajuan->format('d M Y H:i') }}</p>
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                @foreach($p->details->groupBy('product_id') as $prodId => $detailsGroup)
                                    <span class="text-xs bg-slate-50 border border-slate-100 text-slate-700 px-2 py-1 rounded">
                                        {{ $detailsGroup->first()->product->nama_barang }} ({{ $detailsGroup->count() }} unit)
                                    </span>
                                @endforeach
                            </div>
                            @if($p->alasan_override)
                                <p class="text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded px-2.5 py-1.5 mt-2">
                                    <strong>Alasan Override:</strong> "{{ $p->alasan_override }}"
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('borrowings.show', $p->id) }}" class="btn-sm btn-secondary">Detail</a>
                            
                            {{-- Quick Approve --}}
                            <form method="POST" action="{{ route('borrowings.approveManager', $p->id) }}" onsubmit="return confirm('Setujui peminjaman ini?')">
                                @csrf
                                <button type="submit" class="btn-sm btn-primary">Setujui</button>
                            </form>
                            
                            {{-- Quick Reject --}}
                            <div x-data="{ openReject: false }" class="relative">
                                <button @click="openReject = !openReject" type="button" class="btn-sm btn-danger">Tolak</button>
                                <div x-show="openReject" @click.away="openReject = false" class="absolute right-0 bottom-full mb-2 w-72 bg-white rounded-xl shadow-xl border border-gray-100 p-4 z-50">
                                    <p class="text-xs font-semibold text-gray-900 mb-2">Alasan Penolakan Manager</p>
                                    <form method="POST" action="{{ route('borrowings.rejectManager', $p->id) }}">
                                        @csrf
                                        <textarea name="alasan_penolakan" required placeholder="Tulis alasan penolakan..." class="form-input text-xs w-full h-16 resize-none mb-3"></textarea>
                                        <div class="flex justify-end gap-1.5">
                                            <button @click="openReject = false" type="button" class="btn-xs btn-secondary">Batal</button>
                                            <button type="submit" class="btn-xs btn-danger">Kirim Tolak</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- GRAFIK + AUDIT TRAIL --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <div class="card lg:col-span-2">
            <div class="card-header">
                <p class="card-title">Aktivitas Peminjaman</p>
                <span class="text-xs text-gray-400">Tahun {{ date('Y') }}</span>
            </div>
            <div class="card-body">
                <canvas id="borrowingChartMgr" height="220"></canvas>
            </div>
        </div>

        {{-- Audit: Override FIFO --}}
        <div class="card">
            <div class="card-header">
                <p class="card-title">Audit Override FIFO</p>
                <span class="badge badge-warning">{{ $overrides->count() }} kali</span>
            </div>
            <div class="divide-y divide-gray-50 max-h-[250px] overflow-y-auto">
                @forelse($overrides as $o)
                    <div class="px-5 py-3">
                        <p class="text-sm font-medium text-gray-800">{{ $o->borrower->name }}</p>
                        <p class="text-xs text-gray-500 truncate">"{{ $o->alasan_override }}"</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $o->approved_at?->format('d M Y') ?? '—' }}</p>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-xs text-gray-400">Tidak ada audit override FIFO saat ini.</div>
                @endforelse
                <div class="px-5 py-3">
                    <a href="{{ route('reports.index') }}" class="text-sm text-telkom-600 hover:text-telkom-700 font-medium">
                        Lihat laporan lengkap →
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- INSIDEN & KERUGIAN ASET --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
            <div class="card-header">
                <p class="card-title">Laporan Insiden Terbaru</p>
                <a href="{{ route('incidents.index') }}" class="text-xs text-telkom-600 font-medium">Semua →</a>
            </div>
            <div class="divide-y divide-gray-50 max-h-[300px] overflow-y-auto">
                @forelse($incidents as $inc)
                    @php
                        $badgeColor = match($inc->status->value) {
                            'menunggu_verifikasi_staff'  => 'badge-menunggu',
                            'terverifikasi_staff'        => 'badge-terverifikasi',
                            'menunggu_finalisasi_admin' => 'badge-menunggu-admin',
                            'difinalisasi_admin'         => 'badge-selesai',
                            default                      => 'badge-secondary',
                        };
                    @endphp
                    <div class="px-5 py-3 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-800 truncate">
                                {{ $inc->productUnit?->kode_unit ?? '—' }} · {{ ucfirst(str_replace('_', ' ', $inc->jenis->value)) }}
                            </p>
                            <p class="text-xs text-gray-400 truncate">Pelapor: {{ $inc->reporter->name }}</p>
                        </div>
                        <span class="badge {{ $badgeColor }} shrink-0 text-[10px]">{{ ucfirst(str_replace('_', ' ', $inc->status->value)) }}</span>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-xs text-gray-400">Tidak ada laporan insiden saat ini.</div>
                @endforelse
            </div>
        </div>

        {{-- Kerugian Aset (Write-off) --}}
        <div class="card">
            <div class="card-header">
                <p class="card-title">Kerugian Aset (Write-off)</p>
                <span class="text-xs text-gray-400">Tahun {{ date('Y') }}</span>
            </div>
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-3xl font-bold text-red-600">Rp {{ number_format($totalKerugian, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $writeoffs->count() }} unit di-write-off</p>
                    </div>
                </div>
                <div class="space-y-2 max-h-[190px] overflow-y-auto">
                    @forelse($writeoffs as $w)
                        <div class="flex items-center justify-between py-2 border-t border-gray-100">
                            <div class="min-w-0 mr-2">
                                <p class="text-sm text-gray-700 truncate">{{ $w->product->nama_barang }} ({{ $w->kode_unit }})</p>
                                <p class="text-xs text-gray-400">{{ $w->updated_at->format('d M Y') }}</p>
                            </div>
                            <span class="text-sm font-semibold text-red-600 shrink-0">Rp {{ number_format($w->harga_perolehan, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-gray-400 border-t border-gray-100">Belum ada kerugian aset tercatat.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- PENGADAAN & ANALITIK --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Procurement Card --}}
        <div class="card">
            <div class="card-header border-b border-gray-100">
                <p class="card-title">Rekomendasi Pengadaan Barang (Restock)</p>
                <span class="text-xs text-gray-400">Barang di bawah stok minimum</span>
            </div>
            <div class="card-body py-0 divide-y divide-gray-50 max-h-[300px] overflow-y-auto">
                @forelse($lowStocks as $ls)
                    <div x-data="{ openRequest: false }" class="py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-800 truncate">{{ $ls->nama_barang }}</p>
                                <p class="text-xs text-gray-400">Min. Stok: {{ $ls->stok_minimum }} unit · Tersedia: <strong class="text-red-600">{{ $ls->units_count }} unit</strong></p>
                            </div>
                            <button x-show="!openRequest" @click="openRequest = true" class="btn-xs btn-primary shrink-0">+ Ajukan Pengadaan</button>
                        </div>
                        
                        {{-- Inline Form --}}
                        <div x-show="openRequest" x-transition class="bg-gray-50 border border-gray-100 rounded-lg p-3 mt-2">
                            <form method="POST" action="{{ route('procurement.store', $ls->id) }}" class="flex items-end gap-3">
                                @csrf
                                <div class="flex-1">
                                    <label class="text-[10px] font-bold text-gray-500 block mb-1">JUMLAH UNIT PENGADAAN</label>
                                    <input name="qty" type="number" required value="{{ max(1, $ls->stok_minimum - $ls->units_count) }}" min="1" class="form-input text-xs w-full py-1 px-2.5 h-8" />
                                </div>
                                <div class="flex gap-1.5 shrink-0">
                                    <button @click="openRequest = false" type="button" class="btn-xs btn-secondary h-8">Batal</button>
                                    <button type="submit" class="btn-xs btn-primary h-8">Kirim</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-gray-400">Semua barang memiliki stok yang aman.</div>
                @endforelse
            </div>
            
            {{-- Active procurements list --}}
            @if($activeProcurements->isNotEmpty())
                <div class="border-t border-gray-100 p-4 bg-gray-50/50 rounded-b-xl">
                    <p class="text-xs font-bold text-gray-700 mb-2">Pengajuan Aktif:</p>
                    <div class="space-y-1.5 max-h-[120px] overflow-y-auto">
                        @foreach($activeProcurements as $ap)
                            <div class="flex items-center justify-between text-xs bg-white border border-gray-100 rounded p-2">
                                <span>{{ $ap->product->nama_barang }} ({{ $ap->quantity }} unit)</span>
                                <span class="badge badge-menunggu text-[9px] capitalize">{{ $ap->status }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Advanced Analytics Card --}}
        <div class="card">
            <div class="card-header border-b border-gray-100">
                <p class="card-title">Analitik Keandalan & Utilisasi Aset</p>
                <span class="text-xs text-gray-400">KPI Performance Aset</span>
            </div>
            <div class="card-body space-y-4">
                {{-- Top Utilisasi --}}
                <div>
                    <p class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Top 3 Utilisasi Aktif</p>
                    <div class="space-y-2">
                        @foreach($productUtils->take(3) as $pu)
                            <div>
                                <div class="flex justify-between text-xs text-gray-600 mb-1">
                                    <span>{{ $pu['nama'] }}</span>
                                    <span class="font-semibold">{{ $pu['active_borrowed'] }}/{{ $pu['total_units'] }} unit ({{ $pu['active_percentage'] }}%)</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $pu['active_percentage'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Keandalan Aset per Kategori --}}
                <div class="pt-2">
                    <p class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Keandalan Aset & Kerugian per Kategori</p>
                    <div class="space-y-2 max-h-[140px] overflow-y-auto">
                        @foreach($categoryReliability as $cr)
                            <div class="flex justify-between items-center text-xs py-1.5 border-b border-gray-50 last:border-0">
                                <div>
                                    <span class="font-medium text-gray-800">{{ $cr['nama'] }}</span>
                                    <p class="text-[10px] text-gray-400">{{ $cr['incident_count'] }} insiden · {{ $cr['write_off_count'] }} write-off</p>
                                </div>
                                @if($cr['total_loss'] > 0)
                                    <span class="font-semibold text-red-600">Rp {{ number_format($cr['total_loss'], 0, ',', '.') }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('borrowingChartMgr');
    if (!ctx) return;

    // Set global font family to SF Pro / System
    Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "SF Pro Display", "Segoe UI", Roboto, sans-serif';
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#64748B';

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'],
            datasets: [
                {
                    label: 'Pengajuan Baru',
                    data: @json($monthlyBorrowings),
                    backgroundColor: '#E10600',
                    borderRadius: 6,
                    borderSkipped: 'bottom',
                    barThickness: 12,
                },
                {
                    label: 'Aset Kembali',
                    data: @json($monthlyReturns),
                    backgroundColor: '#94A3B8',
                    borderRadius: 6,
                    borderSkipped: 'bottom',
                    barThickness: 12,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'top', 
                    align: 'end',
                    labels: { 
                        usePointStyle: true, 
                        boxWidth: 6,
                        padding: 15,
                        font: {
                            weight: '500'
                        }
                    } 
                },
                tooltip: {
                    backgroundColor: '#1E293B',
                    padding: 10,
                    bodyFont: { family: '-apple-system, BlinkMacSystemFont, "SF Pro Display", "Segoe UI", Roboto, sans-serif' },
                    titleFont: { family: '-apple-system, BlinkMacSystemFont, "SF Pro Display", "Segoe UI", Roboto, sans-serif', weight: '600' }
                }
            },
            scales: {
                x: { 
                    grid: { display: false },
                    border: { display: false }
                },
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#F1F5F9' },
                    border: { display: false }
                },
            },
        },
    });
});
</script>
@endpush
