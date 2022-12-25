<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MasterData extends Model
{
    public function scopeProvince($query)
    {
        $query = DB::connection('mysql2')->table('wil_provinces')->get();
        return $query;
    }

    public function scopeCity($query, $province_id)
    {
        if ($province_id !== null) {
            $query = DB::connection('mysql2')->table('wil_city')->where('province_id', $province_id)->get();
        } else {
            $query = DB::connection('mysql2')->table('wil_city')->get();
        }

        return $query;
    }

    public function scopeGetResto($query)
    {
        $query = DB::connection('mysql2')->table('resto')
            ->where('status',1)
            ->get();
        return $query;
    }

    public function scopeGetRestoMenu($query,$idResto)
    {
        $query = DB::connection('mysql2')->table('resto_menu')
        ->where('id_resto', $idResto)
        ->where('status',1)
        ->get();
        return $query;
    }

}
