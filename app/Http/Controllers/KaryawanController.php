<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawan = DB::table('karyawan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->orderBy('nama_lengkap')
            ->paginate(10);
        $departemen = DB::table('departemen')->get();
        $lokasi = DB:: table('lokasi')->orderBy('kode')->get();
        return view('karyawan.index', compact('karyawan', 'departemen','lokasi'));
    }

    public function store(Request $request)
    {
        $nik = $request->nik;
        $nama_lengkap = $request->nama_lengkap;
        $jabatan = $request->jabatan;
        $no_hp = $request->no_hp;
        $kode_dept = $request->kode_dept;
        $password = Hash::make('12345');
        $kode = $request->kode;
        $foto = null;

        if ($request->hasFile('foto')) {
            $foto = $nik . "." . $request->file('foto')->getClientOriginalExtension();
        }

        try {
            $data = [
                'nik' => $nik,
                'nama_lengkap' => $nama_lengkap,
                'jabatan' => $jabatan,
                'no_hp' => $no_hp,
                'kode_dept' => $kode_dept,
                'foto' => $foto,
                'password' => $password,
                'kode'=> $kode
            ];

            // Cek apakah NIK sudah ada
            $exists = DB::table('karyawan')->where('nik', $nik)->exists();

            if ($exists) {
                return Redirect::back()->with(['warning' => 'Data dengan Nik ' . $nik . ' Sudah Ada']);
            }

            $simpan = DB::table('karyawan')->insert($data);

            if ($simpan) {
                if ($request->hasFile('foto')) {
                    $folderPath = "public/uploads/karyawan/";
                    $request->file('foto')->storeAs($folderPath, $foto);
                }
                return Redirect::back()->with(['success' => 'Data Berhasil Disimpan']);
            }
        } catch (\Exception $e) {
           // $message = ($e->getCode() == 23000) ? "Data dengan Nik " . $nik . " Sudah Ada" : "Hubungi IT";
            return Redirect::back()->with(['warning' => 'Data Gagal Disimpan. ']);
        }
    }

    public function edit(Request $request)
    {
        $nik = $request->nik;
        $departemen = DB::table('departemen')->get();
        $lokasi = DB:: table('lokasi')->orderBy('kode')->get();
        $karyawan = DB::table('karyawan')->where('nik', $nik)->first();
        return view('karyawan.edit', compact('departemen', 'karyawan','lokasi'));
    }

    public function update(Request $request, $nik)
{
    $nama_lengkap = $request->nama_lengkap;
    $jabatan = $request->jabatan;
    $no_hp = $request->no_hp;
    $kode_dept = $request->kode_dept;
    $kode = $request->kode;
    $old_foto = $request->old_foto;

    if ($request->hasFile('foto')) {
        $foto = $nik . "." . $request->file('foto')->getClientOriginalExtension();
    } else {
        $foto = $old_foto;
    }

    $data = [
        'nama_lengkap' => $nama_lengkap,
        'jabatan' => $jabatan,
        'no_hp' => $no_hp,
        'kode_dept' => $kode_dept,
        'kode'=> $kode,
        'foto' => $foto
    ];

    try {
        $update = DB::table('karyawan')->where('nik', $nik)->update($data);

        if ($update) {
            if ($request->hasFile('foto')) {
                $folderPath = "public/uploads/karyawan/";
                $folderPathOld = "public/uploads/karyawan/" . $old_foto;
                Storage::delete($folderPathOld);
                $request->file('foto')->storeAs($folderPath, $foto);
            }
            return Redirect::back()->with(['success' => 'Data Berhasil Update']);
        }
    } catch (\Exception $e) {
        return Redirect::back()->with(['warning' => 'Data Gagal Diupdate']);
    }
}

public function delete($nik)
{
    $delete = DB::table('karyawan')->where('nik', $nik)->delete();
    if ($delete) {
        return Redirect::back()->with(['success' => 'Data Berhasil Dihapus']);
    } else {
        return Redirect::back()->with(['warning' => 'Data Gagal Dihapus']);
    }
}


    }
    





