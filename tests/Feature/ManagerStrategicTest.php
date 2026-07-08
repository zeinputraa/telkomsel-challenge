<?php

use App\Enums\JenisInsiden;
use App\Enums\StatusBorrowing;
use App\Enums\StatusInsiden;
use App\Enums\StatusUnit;
use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\Category;
use App\Models\IncidentReport;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Ambil Roles hasil seeding global
    $this->adminRole = Role::where('name', 'admin')->first();
    $this->managerRole = Role::where('name', 'manager')->first();
    $this->staffRole = Role::where('name', 'staff')->first();
    $this->karyawanRole = Role::where('name', 'karyawan')->first();

    $this->adminUser = User::factory()->create(['role_id' => $this->adminRole->id]);
    $this->managerUser = User::factory()->create(['role_id' => $this->managerRole->id]);
    $this->staffUser = User::factory()->create(['role_id' => $this->staffRole->id]);
    $this->karyawan = User::factory()->create(['role_id' => $this->karyawanRole->id]);

    // Product & Unit
    $this->category = Category::factory()->create();

    // Create high-value product (e.g. unit price Rp 15,000,000)
    $this->highValueProduct = Product::factory()->create(['category_id' => $this->category->id]);
    ProductUnit::factory()->create([
        'product_id' => $this->highValueProduct->id,
        'harga_perolehan' => 15000000,
        'status' => StatusUnit::Tersedia->value,
    ]);

    // Create normal product (e.g. unit price Rp 1,500,000)
    $this->normalProduct = Product::factory()->create(['category_id' => $this->category->id]);
    ProductUnit::factory()->create([
        'product_id' => $this->normalProduct->id,
        'harga_perolehan' => 1500000,
        'status' => StatusUnit::Tersedia->value,
    ]);
});

it('flags high-value borrowing requests for manager approval', function () {
    // Submit borrowing request with high-value item
    $response = $this->actingAs($this->karyawan)->post(route('borrowings.store'), [
        'tanggal_pinjam_rencana' => now()->addDays(1)->format('Y-m-d'),
        'tanggal_kembali_rencana' => now()->addDays(5)->format('Y-m-d'),
        'catatan' => 'Peminjaman Laptop High-End',
        'items' => [
            [
                'product_id' => $this->highValueProduct->id,
                'qty' => 1,
            ],
        ],
    ]);

    $response->assertRedirect(route('borrowings.my'));

    $borrowing = Borrowing::latest('id')->first();
    expect($borrowing->needs_manager_approval)->toBeTrue();
    expect($borrowing->status->value)->toBe(StatusBorrowing::Diajukan->value);
});

it('does not flag normal-value borrowing requests for manager approval', function () {
    // Submit borrowing request with normal-value item
    $response = $this->actingAs($this->karyawan)->post(route('borrowings.store'), [
        'tanggal_pinjam_rencana' => now()->addDays(1)->format('Y-m-d'),
        'tanggal_kembali_rencana' => now()->addDays(5)->format('Y-m-d'),
        'catatan' => 'Peminjaman Laptop Normal',
        'items' => [
            [
                'product_id' => $this->normalProduct->id,
                'qty' => 1,
            ],
        ],
    ]);

    $response->assertRedirect(route('borrowings.my'));

    $borrowing = Borrowing::latest('id')->first();
    expect($borrowing->needs_manager_approval)->toBeFalse();
});

it('allows managers to approve high-value borrowings', function () {
    // Create borrowing that needs manager approval
    $borrowing = Borrowing::factory()->create([
        'user_id' => $this->karyawan->id,
        'needs_manager_approval' => true,
        'status' => StatusBorrowing::Diajukan->value,
    ]);

    BorrowingDetail::factory()->create([
        'borrowing_id' => $borrowing->id,
        'product_id' => $this->highValueProduct->id,
        'status' => 'diajukan',
    ]);

    $response = $this->actingAs($this->managerUser)->post(route('borrowings.approveManager', $borrowing->id));

    $response->assertRedirect(route('borrowings.show', $borrowing->id));

    $borrowing->refresh();
    expect($borrowing->status->value)->toBe(StatusBorrowing::Disetujui->value);
    expect($borrowing->manager_approved)->toBeTrue();
    expect($borrowing->manager_approved_by)->toBe($this->managerUser->id);
});

it('allows managers to reject high-value borrowings with reason', function () {
    $borrowing = Borrowing::factory()->create([
        'user_id' => $this->karyawan->id,
        'needs_manager_approval' => true,
        'status' => StatusBorrowing::Diajukan->value,
    ]);

    BorrowingDetail::factory()->create([
        'borrowing_id' => $borrowing->id,
        'product_id' => $this->highValueProduct->id,
        'status' => 'diajukan',
    ]);

    $response = $this->actingAs($this->managerUser)->post(route('borrowings.rejectManager', $borrowing->id), [
        'alasan_penolakan' => 'Anggaran ketat atau tidak mendesak.',
    ]);

    $response->assertRedirect(route('borrowings.show', $borrowing->id));

    $borrowing->refresh();
    expect($borrowing->status->value)->toBe(StatusBorrowing::Ditolak->value);
    expect($borrowing->manager_approved)->toBeFalse();
    expect($borrowing->manager_alasan_penolakan)->toBe('Anggaran ketat atau tidak mendesak.');
});

it('suspends staff approval when FIFO override is requested and requests manager authorization', function () {
    // Create borrowing request
    $borrowing = Borrowing::factory()->create([
        'user_id' => $this->karyawan->id,
        'needs_manager_approval' => false,
        'status' => StatusBorrowing::Diajukan->value,
    ]);

    $detail = BorrowingDetail::factory()->create([
        'borrowing_id' => $borrowing->id,
        'product_id' => $this->normalProduct->id,
        'status' => 'diajukan',
    ]);

    // Staff approves with fifo_override
    $response = $this->actingAs($this->staffUser)->post(route('borrowings.approve', $borrowing->id), [
        'fifo_override' => '1',
        'alasan_override' => 'Mendesak untuk direktur',
    ]);

    $response->assertRedirect(route('borrowings.show', $borrowing->id));

    $borrowing->refresh();
    expect($borrowing->status->value)->toBe(StatusBorrowing::Diajukan->value); // Suspended
    expect($borrowing->needs_manager_approval)->toBeTrue();
    expect($borrowing->fifo_override)->toBeTrue();
    expect($borrowing->alasan_override)->toBe('Mendesak untuk direktur');
});

it('allows managers to finalize incidents', function () {
    $unit = ProductUnit::factory()->create([
        'product_id' => $this->normalProduct->id,
        'status' => StatusUnit::Dipinjam->value,
    ]);

    $borrowing = Borrowing::factory()->berjalan()->create([
        'user_id' => $this->karyawan->id,
    ]);

    $detail = BorrowingDetail::factory()->dipinjam()->create([
        'borrowing_id' => $borrowing->id,
        'product_id' => $this->normalProduct->id,
        'product_unit_id' => $unit->id,
    ]);

    $report = IncidentReport::create([
        'borrowing_detail_id' => $detail->id,
        'product_unit_id' => $unit->id,
        'reported_by' => $this->karyawan->id,
        'jenis' => JenisInsiden::Hilang->value,
        'kronologi' => 'Hilang di taksi',
        'status' => StatusInsiden::MenungguFinalisasiAdmin->value,
    ]);

    // Manager finalizes
    $response = $this->actingAs($this->managerUser)->post(route('incidents.finalize', $report->id), [
        'status_final' => 'write_off',
    ]);

    $response->assertRedirect(route('incidents.show', $report->id));

    $report->refresh();
    expect($report->status->value)->toBe(StatusInsiden::DifinalisasiAdmin->value);
});

it('allows managers to submit procurement requests', function () {
    $response = $this->actingAs($this->managerUser)->post(route('procurement.store', $this->normalProduct->id), [
        'qty' => 5,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('procurement_requests', [
        'product_id' => $this->normalProduct->id,
        'quantity' => 5,
        'requested_by' => $this->managerUser->id,
        'status' => 'pending',
    ]);
});

it('allows admins to approve procurement requests and registers units automatically', function () {
    $procurement = \App\Models\ProcurementRequest::create([
        'product_id' => $this->normalProduct->id,
        'quantity' => 3,
        'requested_by' => $this->managerUser->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->adminUser)->post(route('procurement.approve', $procurement->id), [
        'harga_perolehan' => 1200000,
        'lokasi_penyimpanan' => 'Gudang IT Lt. 2',
    ]);

    $response->assertRedirect();

    $procurement->refresh();
    expect($procurement->status)->toBe('completed');

    // Asserts 3 units were created for this product
    $this->assertDatabaseCount('product_units', 5); // 2 seeded in beforeEach + 3 new
    $this->assertDatabaseHas('product_units', [
        'product_id' => $this->normalProduct->id,
        'harga_perolehan' => 1200000,
        'lokasi_penyimpanan' => 'Gudang IT Lt. 2',
        'status' => 'tersedia',
        'kondisi' => 'baik',
    ]);
});

it('allows admins to reject procurement requests', function () {
    $procurement = \App\Models\ProcurementRequest::create([
        'product_id' => $this->normalProduct->id,
        'quantity' => 2,
        'requested_by' => $this->managerUser->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->adminUser)->post(route('procurement.reject', $procurement->id));

    $response->assertRedirect();

    $procurement->refresh();
    expect($procurement->status)->toBe('rejected');
});
