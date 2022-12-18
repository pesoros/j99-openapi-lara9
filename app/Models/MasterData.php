<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MasterData extends Model
{
    public function scopeProvince($query)
    {
        $query = DB::table('wil_provinces')->get();
        return $query;
    }

    public function scopeCity($query, $province_id)
    {
        if ($province_id !== null) {
            $query = DB::table('wil_city')->where('province_id', $province_id)->get();
        } else {
            $query = DB::table('wil_city')->get();
        }

        return $query;
    }

}
