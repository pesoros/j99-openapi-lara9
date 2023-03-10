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
        $query = DB::connection('mysql2')
            ->table('wil_city AS city')
            ->selectRaw('tl.id as poolId, tl.name as poolName, city.id as cityId, city.name AS namaKota')
            ->join('trip_location AS tl','tl.city', '=', 'city.id')
            ->get();
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

    public function scopePoolPair($query)
    {
        $query = DB::connection('mysql2')->table('trip_point as tpoint')
        ->selectRaw('
            tpoint.dep_point as sourcePool, 
            tpoint.arr_point as destPool,
            tl1.id as sourcePoolId,
            tl2.id as destPoolId
        ')
        ->join('trip_location AS tl1','tl1.name', '=', 'tpoint.dep_point')
        ->join('trip_location AS tl2','tl2.name', '=', 'tpoint.arr_point')
        ->where('tpoint.dep_point', '!=', 'NULL')
        ->where('tpoint.arr_point', '!=', 'NULL')
        ->groupBy('sourcePool')
        ->groupBy('destPool')
        ->get();
        return $query;
    }

}
