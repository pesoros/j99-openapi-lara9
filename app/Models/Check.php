<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Check extends Model
{
    public function scopeGetBook($query, $bookingCode)
    {
        $query = DB::connection('mysql2')->table('tkt_booking_head')
            ->where('booking_code',$bookingCode)
            ->first();
        return $query;
    }

    public function scopeDetailBook($query, $bookingCode)
    {
        $query = DB::connection('mysql2')->table('tkt_booking')
            ->where('booking_code',$bookingCode)
            ->first();
        return $query;
    }

    public function scopeGetPaymentRegis($query, $bookingCode)
    {
        $query = DB::connection('mysql2')->table('payment_registration')
            ->selectRaw('
                payment_method,
                payment_channel_code,
                va_number,
                dekstop_link,
                mobile_link
            ')
            ->where('booking_code',$bookingCode)
            ->get();
        return $query;
    }

    public function scopeGetTicket($query, $bookingCode)
    {
        $query = DB::connection('mysql2')->table('tkt_passenger_pcs AS tps')
            ->selectRaw("
                tbook.booking_code,
                tps.name,
                tps.phone,
                tps.ticket_number,
                ft.type,
                tps.seat_number,
                tbook.pickup_trip_location,
                tbook.drop_trip_location,
                tbook.booking_date,
                tps.baggage,
                IF(tps.baggage = 1, 'Bawa', 'Tidak Bawa') as baggage,
                resto.food_name,
                tbook.price,
                tbook.adult,
                trip.trip_id,
                r.resto_name,
                tpoint.dep_time,
                tpoint.arr_time
            ")
            ->leftJoin('tkt_booking AS tbook', 'tps.booking_id', '=', 'tbook.id_no')
            ->leftJoin('trip', 'tbook.trip_id_no', '=', 'trip.trip_id')
            ->leftJoin('fleet_type AS ft', 'tps.fleet_type', '=', 'ft.id')
            ->leftJoin('resto_menu AS resto', 'tps.food', '=', 'resto.id')
            ->leftJoin('resto AS r', 'r.id', '=', 'resto.id_resto')
            ->leftJoin('trip_assign AS tras', 'tbook.trip_id_no', '=', 'tras.trip')
            ->leftJoin('trip_point AS tpoint', function($join)
            {
                $join->on('tpoint.trip_assign_id', '=', 'tras.id')->where('tpoint.dep_point', '=', 'tbook.pickup_trip_location')->where('tpoint.arr_point', '=', 'tbook.drop_trip_location');
            })
            ->where('tbook.booking_code', $bookingCode)
            ->get();
        return $query;
    }

    public function getHour($query,$trip,$pickup,$drop)
    {
        $query = DB::connection('mysql2')->table('trip_point')
            ->selectRaw("
                trip_point.dep_time,
                trip_point.arr_time
            ")
            ->join('trip_assign', 'trip_assign.id = trip_point.trip_assign_id')
            ->join('trip', 'trip.trip_id = trip_assign.trip')
            ->where('trip_point.dep_point',$pickup)
            ->where('trip_point.arr_point',$drop)
            ->where('trip.trip_id',$trip)
            ->get();
        return $query;
    }
}
