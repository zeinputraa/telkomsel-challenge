<?php

namespace App\Models;

use App\Enums\JenisLaporan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportArchive extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'jenis',
        'periode_mulai',
        'periode_selesai',
        'total_nilai_aset',
        'total_kerugian',
        'file_pdf_path',
        'file_excel_path',
        'generated_by',
        'generated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'periode_mulai' => 'date',
            'periode_selesai' => 'date',
            'generated_at' => 'datetime',
            'jenis' => JenisLaporan::class,
        ];
    }

    /**
     * Get the user who generated the report archive.
     *
     * @return BelongsTo<User, $this>
     */
    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
