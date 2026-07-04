<?php

namespace App\Enums;

enum JenisLaporan: string
{
    case Bulanan = 'bulanan';
    case Kuartalan = 'kuartalan';
    case Tahunan = 'tahunan';
    case Custom = 'custom';
}
