<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class LokasiController extends Controller
{
    public function index()
    {
        $lokasi = DB::table('lokasi')->orderBy('kode')->get();
        return view('lokasi.index', compact('lokasi'));
    }


    public function store(Request $request)
    {
        $kode = $request->kode;
        $nama = $request->nama;
        $lokasi = $request->lokasi;
        $radius = $request->radius;

        try {
            $data = [
                'kode' => $kode,
                'nama' => $nama,
                'lokasi' => $lokasi,
                'radius' => $radius
            ];
            DB::table('lokasi')->insert($data);
            return Redirect::back()->with(['success' => 'Data Berhasil Disimpan']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['warning' => 'Data Gagal Disimpan']);
        }
    }

    public function edit(Request $request)
    {
        $kode = $request->kode;
        $lokasi = DB::table('lokasi')->where('kode', $kode)->first();
        return view('lokasi.edit', compact('lokasi'));
    }

    public function update(Request $request)
    {
        $kode = $request->kode;
        $nama = $request->nama;
        $lokasi = $request->lokasi;
        $radius = $request->radius;

        try {
            $data = [
                'nama' => $nama,
                'lokasi' => $lokasi,
                'radius' => $radius
            ];
            DB::table('lokasi')
                ->where('kode', $kode)
                ->update($data);
            return Redirect::back()->with(['success' => 'Data Berhasil Diupdate']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['warning' => 'Data Gagal Diupdate']);
        }
    }

    public function delete($kode)
    {
        $hapus = DB::table('lokasi')->where('kode', $kode)->delete();
        if ($hapus) {
            return Redirect::back()->with(['success' => 'Data Berhasil Di Hapus']);
        } else {
            return Redirect::back()->with(['warning' => 'Data Gagal Di Hapus']);
        }
    }
}
