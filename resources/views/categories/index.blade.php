@extends('layouts.app')

@php $pageTitle = 'Kategori Barang'; @endphp

@section('content')
<div class="space-y-5">
    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Kategori Barang</h1>
            <p class="page-subtitle">Kelola pengelompokan jenis barang inventaris</p>
        </div>
        @if(auth()->user()->hasRole('admin', 'staff'))
            <a href="{{ route('categories.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Kategori
            </a>
        @endif
    </div>

    {{-- Table --}}
    <div class="table-wrapper">
        <div class="card-header px-5 py-4 flex items-center justify-between border-b border-gray-100">
            <p class="card-title">Daftar Kategori</p>
            <span class="text-xs text-gray-400">{{ $categories->total() }} kategori</span>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Jumlah Produk</th>
                    @if(auth()->user()->hasRole('admin', 'staff'))
                        <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-50">
                @forelse($categories as $i => $cat)
                    <tr>
                        <td class="text-gray-400 text-xs">{{ $categories->firstItem() + $i }}</td>
                        <td class="font-medium text-gray-900">{{ $cat->nama_kategori }}</td>
                        <td class="text-gray-500 max-w-xs truncate">{{ $cat->deskripsi ?? '—' }}</td>
                        <td>
                            <span class="badge badge-manager">{{ $cat->products_count ?? '—' }} barang</span>
                        </td>
                        @if(auth()->user()->hasRole('admin', 'staff'))
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('categories.edit', $cat) }}"
                                       class="btn-sm btn-secondary">Edit</a>
                                    <form method="POST" action="{{ route('categories.destroy', $cat) }}"
                                          onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-sm btn-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->hasRole('admin', 'staff') ? 5 : 4 }}">
                            <div class="empty-state">
                                <svg class="empty-state-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <p class="empty-state-title">Belum ada kategori</p>
                                @if(auth()->user()->hasRole('admin', 'staff'))
                                    <a href="{{ route('categories.create') }}" class="btn-primary btn-sm mt-3">Buat Kategori</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($categories->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
