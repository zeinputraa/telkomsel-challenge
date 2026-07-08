@extends('layouts.app')

@php $pageTitle = 'Kelola Peminjaman'; @endphp

@section('content')
<div class="space-y-5">

    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Kelola Peminjaman</h1>
            <p class="page-subtitle">Daftar semua pengajuan peminjaman barang inventaris</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div x-data="{ tab: 'semua' }">
        <div class="tabs">
            <button @click="tab='semua'"     :class="tab==='semua'     ? 'active' : ''" class="tab-link">Semua</button>
            <button @click="tab='diajukan'"  :class="tab==='diajukan'  ? 'active' : ''" class="tab-link">Menunggu Approval</button>
            <button @click="tab='berjalan'"  :class="tab==='berjalan'  ? 'active' : ''" class="tab-link">Berjalan</button>
            <button @click="tab='terlambat'" :class="tab==='terlambat' ? 'active' : ''" class="tab-link text-red-600">Terlambat</button>
            <button @click="tab='selesai'"   :class="tab==='selesai'   ? 'active' : ''" class="tab-link">Selesai</button>
        </div>

        {{-- Table --}}
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th class="pl-5 w-1/4">Peminjam / Kode</th>
                        <th class="w-1/3">Barang Dipinjam</th>
                        <th>Periode Pinjam</th>
                        <th>Status</th>
                        <th class="text-right pr-5">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @php
                        $badgeMap = [
                            'diajukan' => 'badge-diajukan',
                            'disetujui' => 'badge-disetujui',
                            'berjalan' => 'badge-berjalan',
                            'selesai' => 'badge-selesai',
                            'ditolak' => 'badge-ditolak',
                            'dibatalkan_user' => 'badge-dibatalkan',
                            'dibatalkan_otomatis' => 'badge-dibatalkan',
                        ];
                        $labelMap = [
                            'diajukan' => 'Menunggu Approval',
                            'disetujui' => 'Disetujui',
                            'berjalan' => 'Berjalan',
                            'selesai' => 'Selesai',
                            'ditolak' => 'Ditolak',
                            'dibatalkan_user' => 'Dibatalkan',
                            'dibatalkan_otomatis' => 'Dibatalkan Otomatis',
                        ];
                    @endphp
                    @forelse($borrowings as $b)
                        @php
                            $statusVal = $b->status->value;
                            $tabVal = $statusVal;
                            if ($statusVal === 'berjalan' && $b->tanggal_kembali_rencana && $b->tanggal_kembali_rencana->isPast()) {
                                $tabVal = 'terlambat';
                            }
                        @endphp
                        <tr x-show="tab === 'semua' || tab === '{{ $tabVal }}'">
                            <td class="pl-5 whitespace-normal">
                                <p class="font-medium text-gray-900">{{ $b->borrower->name }}</p>
                                <p class="font-mono text-[10px] text-gray-400 mt-0.5">{{ $b->kode_peminjaman }}</p>
                            </td>
                            <td class="whitespace-normal">
                                <div class="flex flex-wrap gap-1 items-center">
                                    @foreach($b->details->groupBy('product_id') as $productId => $detailsGroup)
                                        @php $detail = $detailsGroup->first(); @endphp
                                        <div class="inline-flex items-center gap-1 bg-gray-50 border border-gray-100 rounded-md px-2 py-0.5 text-xs text-gray-700">
                                            <span class="font-medium">{{ $detail->product->nama_barang ?? '—' }}</span>
                                            <span class="text-[9px] text-gray-400 font-bold">x{{ $detailsGroup->count() }}</span>
                                        </div>
                                    @endforeach
                                    @if($b->fifo_override)
                                        <span class="badge badge-warning text-[9px] py-0 px-1 ml-1" title="Sistem FIFO Dilewati: {{ $b->alasan_override }}">FIFO Override</span>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-normal">
                                <div class="text-xs text-gray-500 space-y-0.5">
                                    <p>{{ $b->tanggal_pinjam_rencana ? $b->tanggal_pinjam_rencana->format('d M Y') : '—' }} &rarr; {{ $b->tanggal_kembali_rencana ? $b->tanggal_kembali_rencana->format('d M Y') : '—' }}</p>
                                    @if($tabVal === 'terlambat')
                                        <span class="inline-flex items-center text-[10px] font-bold text-red-600 mt-0.5">
                                            <svg class="w-3.5 h-3.5 mr-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            Terlambat
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $badgeMap[$statusVal] ?? '' }}">
                                    {{ $labelMap[$statusVal] ?? $statusVal }}
                                </span>
                            </td>
                            <td class="pr-5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('borrowings.show', $b->id) }}" class="btn-sm btn-secondary font-medium">
                                        {{ in_array($statusVal, ['diajukan']) ? 'Proses' : 'Detail' }}
                                    </a>
                                    @if($statusVal === 'disetujui')
                                        <a href="{{ route('borrowings.handover', $b->id) }}" class="btn-sm btn-success font-medium">Serah Terima</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-gray-400 py-6 text-sm">Tidak ada transaksi peminjaman ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination links --}}
        <div class="mt-4">
            {{ $borrowings->links() }}
        </div>
    </div>
</div>
@endsection
