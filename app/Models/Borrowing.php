<?php

namespace App\Models;

use App\Enums\StatusBorrowing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Borrowing extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'kode_peminjaman',
        'tanggal_pengajuan',
        'tanggal_pinjam_rencana',
        'tanggal_kembali_rencana',
        'status',
        'approved_by',
        'approved_at',
        'fifo_override',
        'alasan_override',
        'alasan_penolakan',
        'catatan',
        'needs_manager_approval',
        'manager_approved',
        'manager_approved_by',
        'manager_approved_at',
        'manager_alasan_penolakan',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'datetime',
            'tanggal_pinjam_rencana' => 'date',
            'tanggal_kembali_rencana' => 'date',
            'approved_at' => 'datetime',
            'fifo_override' => 'boolean',
            'status' => StatusBorrowing::class,
            'needs_manager_approval' => 'boolean',
            'manager_approved' => 'boolean',
            'manager_approved_at' => 'datetime',
        ];
    }

    /**
     * Get the borrower (user) who requested this borrowing.
     *
     * @return BelongsTo<User, $this>
     */
    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who approved this borrowing.
     *
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the manager who approved this borrowing.
     *
     * @return BelongsTo<User, $this>
     */
    public function managerApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }

    /**
     * Get the details (assigned units/products) for this borrowing.
     *
     * @return HasMany<BorrowingDetail, $this>
     */
    public function details(): HasMany
    {
        return $this->hasMany(BorrowingDetail::class);
    }
}
