<?php

namespace App\Controllers;

use App\Models\PtsModel;

class Map extends BaseController
{
    protected $pts;

    public function __construct()
    {
        $this->pts = new PtsModel();
    }

    // ✅ HALAMAN UTAMA MAP
    public function index()
    {
        return view('map');
    }

    // ✅ TOTAL DATA SELURUH SUMUT (UNTUK POPUP AWAL & BACK)
    public function totalSumut()
    {
        return $this->response->setJSON(
            $this->pts->getRekapProvinsi()
        );
    }

    // ✅ DATA PER WILAYAH (SAAT DIKLIK)
    public function wilayah($namaWilayah)
    {
        return $this->response->setJSON(
            $this->pts->getRekapWilayah(urldecode($namaWilayah))
        );
    }
}
