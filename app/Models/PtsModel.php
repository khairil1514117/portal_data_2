<?php

namespace App\Models;

use CodeIgniter\Model;

class PtsModel extends Model
{
    protected $table = 'data_pts';
    protected $primaryKey = 'kode_pt';
    protected $allowedFields = ['kode_pt','nama_pt','jenis_pt','wilayah'];

    // ✅ REKAP PER WILAYAH (KAB/KOTA)
    public function getRekapWilayah($wilayah)
    {
        $data = $this->select("
                wilayah,
                COUNT(*) as total_pts,
                SUM(jenis_pt = 'Universitas') as universitas,
                SUM(jenis_pt = 'Institut') as institut,
                SUM(jenis_pt = 'Sekolah Tinggi') as sekolah_tinggi,
                SUM(jenis_pt = 'Politeknik') as politeknik,
                SUM(jenis_pt = 'Akademi') as akademi
            ")
            ->where('wilayah', $wilayah)
            ->groupBy('wilayah')
            ->first();

        // ✅ JIKA DATA KOSONG, KEMBALIKAN NILAI 0 (ANTI ERROR JS)
        return $data ?? [
            'wilayah' => $wilayah,
            'total_pts' => 0,
            'universitas' => 0,
            'institut' => 0,
            'sekolah_tinggi' => 0,
            'politeknik' => 0,
            'akademi' => 0,
        ];
    }

    // ✅ REKAP TOTAL PROVINSI SUMUT
    public function getRekapProvinsi()
    {
        $data = $this->select("
                COUNT(*) as total_pts,
                SUM(jenis_pt = 'Universitas') as universitas,
                SUM(jenis_pt = 'Institut') as institut,
                SUM(jenis_pt = 'Sekolah Tinggi') as sekolah_tinggi,
                SUM(jenis_pt = 'Politeknik') as politeknik,
                SUM(jenis_pt = 'Akademi') as akademi
            ")
            ->first();

        // ✅ JIKA DATABASE KOSONG
        return $data ?? [
            'total_pts' => 0,
            'universitas' => 0,
            'institut' => 0,
            'sekolah_tinggi' => 0,
            'politeknik' => 0,
            'akademi' => 0,
        ];
    }
}
