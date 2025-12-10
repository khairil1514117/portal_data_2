<?php

namespace App\Models;
use CodeIgniter\Model;

class KabupatenModel extends Model
{
    protected $table = 'kabupaten';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nama','jumlah_pts','jumlah_mahasiswa',
        'jumlah_dosen','akreditasi_a','keterangan'
    ];
}
