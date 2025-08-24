<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DeliveryArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'provinsi_id',
        'provinsi_name',
        'kabupaten_id',
        'kabupaten_name',
        'kecamatan_id',
        'kecamatan_name',
        'is_active',
        'shipping_cost',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'shipping_cost' => 'integer',
    ];

    // Scope untuk wilayah aktif
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    // Method untuk mendapatkan provinsi yang tersedia
    public static function getAvailableProvinsi()
    {
        return self::active()
            ->select('provinsi_id', 'provinsi_name')
            ->distinct()
            ->get()
            ->pluck('provinsi_name', 'provinsi_id');
    }

    // Method untuk mendapatkan kabupaten berdasarkan provinsi
    public static function getAvailableKabupaten($provinsiId)
    {
        return self::active()
            ->where('provinsi_id', $provinsiId)
            ->select('kabupaten_id', 'kabupaten_name')
            ->distinct()
            ->get()
            ->pluck('kabupaten_name', 'kabupaten_id');
    }

    // Method untuk mendapatkan kecamatan berdasarkan kabupaten - UPDATED
    public static function getAvailableKecamatan($kabupatenId)
    {
        return self::active()
            ->where('kabupaten_id', $kabupatenId)
            ->whereNotNull('kecamatan_id')
            ->whereNotNull('kecamatan_name')
            ->select('kecamatan_id', 'kecamatan_name')
            ->distinct()
            ->get()
            ->pluck('kecamatan_name', 'kecamatan_id');
    }

    // Method untuk mengecek apakah wilayah tersedia - UPDATED untuk include kecamatan
    public static function isAreaAvailable($provinsiId, $kabupatenId, $kecamatanId = null)
    {
        $query = self::active()
            ->where('provinsi_id', $provinsiId)
            ->where('kabupaten_id', $kabupatenId);

        if ($kecamatanId) {
            $query->where('kecamatan_id', $kecamatanId);
        }

        return $query->exists();
    }

    // Method untuk mendapatkan shipping cost - UPDATED untuk include kecamatan
    public static function getShippingCost($provinsiId, $kabupatenId, $kecamatanId = null)
    {
        $query = self::active()
            ->where('provinsi_id', $provinsiId)
            ->where('kabupaten_id', $kabupatenId);

        if ($kecamatanId) {
            $query->where('kecamatan_id', $kecamatanId);
        }

        $area = $query->first();

        return $area ? $area->shipping_cost : 0;
    }

    // Method untuk mendapatkan detail wilayah lengkap
    public static function getAreaDetails($provinsiId, $kabupatenId, $kecamatanId)
    {
        return self::active()
            ->where('provinsi_id', $provinsiId)
            ->where('kabupaten_id', $kabupatenId)
            ->where('kecamatan_id', $kecamatanId)
            ->first();
    }

    // Method untuk validasi wilayah yang komplit
    public static function validateAreaSelection($provinsiId, $kabupatenId, $kecamatanId)
    {
        // Validasi provinsi
        $provinsiExists = self::active()
            ->where('provinsi_id', $provinsiId)
            ->exists();

        if (!$provinsiExists) {
            return ['valid' => false, 'message' => 'Provinsi tidak tersedia'];
        }

        // Validasi kabupaten
        $kabupatenExists = self::active()
            ->where('provinsi_id', $provinsiId)
            ->where('kabupaten_id', $kabupatenId)
            ->exists();

        if (!$kabupatenExists) {
            return ['valid' => false, 'message' => 'Kabupaten/Kota tidak tersedia'];
        }

        // Validasi kecamatan
        $kecamatanExists = self::active()
            ->where('provinsi_id', $provinsiId)
            ->where('kabupaten_id', $kabupatenId)
            ->where('kecamatan_id', $kecamatanId)
            ->exists();

        if (!$kecamatanExists) {
            return ['valid' => false, 'message' => 'Kecamatan tidak tersedia'];
        }

        return ['valid' => true, 'message' => 'Area valid'];
    }
}

  

//       public static function isAreaAvailable($provinsiId, $kabupatenId)
//     {
//         return self::active()
//             ->where('provinsi_id', $provinsiId)
//             ->where('kabupaten_id', $kabupatenId)
//             ->exists();
//     }

//     //shhipping cost method
//     public static function getShippingCost($provinsiId, $kabupatenId)
//     {
//         $area = self::active()
//             ->where('provinsi_id', $provinsiId)
//             ->where('kabupaten_id', $kabupatenId)
//             ->first();
        
//         return $area ? $area->shipping_cost : 0;
//     }

//     // // Method untuk mengecek apakah wilayah tersedia
//     // public static function isAreaAvailable($provinsiId, $kabupatenId, $kecamatanId = null)
//     // {
//     //     $query = self::active()
//     //         ->where('provinsi_id', $provinsiId)
//     //         ->where('kabupaten_id', $kabupatenId);

//     //     if ($kecamatanId) {
//     //         $query->where(function($q) use ($kecamatanId) {
//     //             $q->where('kecamatan_id', $kecamatanId)
//     //               ->orWhereNull('kecamatan_id');
//     //         });
//     //     }

//     //     return $query->exists();
//     // }
// }