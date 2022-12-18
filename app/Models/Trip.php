<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Trip extends Model
{
    public function scopeListBus($query, $request)
    {
        $kelas = $request->fleet_type;
        $unit_type = $request->unit_type;
        $start = $request->arrival;
        $end = $request->departure;
        $date = $request->date;
        $whereext = '';

        if ($kelas) {
            $whereext .= " AND tp.id = ".$kelas;
        }

        if ($unit_type) {
            $whereext .= " AND fr.unit_id = ".$unit_type;
        }

        $fstart = explode("-",$start);
        $fend = explode("-",$end);

        if (count($fstart) > 1) {
            $whereext .= " AND tl1.name = '".trim($fstart[1])."'";
        } else {
            $whereext .= " AND citydep.name = '".$start."'";
        }

        if (count($fend) > 1) {
            $whereext .= " AND tl2.name = '".trim($fend[1])."'";
        } else {
            $whereext .= " AND cityarr.name = '".$end."'";
        }

        $query = DB::select(
            DB::raw("
                SELECT
                ta.trip_id AS trip_id_no,
                ta.route AS trip_route_id,
                ta.shedule_id,
                tr.name AS trip_route_name,
                tp.id as type,
                tp.total_seat AS fleet_seats,
                tp.type AS class,
                tp.image,
                fr.reg_no AS fleet_registration_id,
                fr.unit_id AS unit_type,
                tr.approximate_time AS duration,
                tr.stoppage_points,
                tr.distance,
                tr.pickup_points,
                tr.dropoff_points,
                tras.closed_by_id,
                tr.resto_id,
                tl1.name AS pickup_trip_location,
                tl2.name AS drop_trip_location,
                tpoint.dep_time as start,
                tpoint.arr_time as end,
                tprs.price as normal_price,
                tprs.price as price,
                tprs.sp_price as sp_price,
                citydep.name as citydep,
                cityarr.name as cityarr,
                trext.price as price_ext,
                tras.sp_day
                FROM trip_point_price AS tprs
                INNER JOIN trip_point AS tpoint ON tpoint.id = tprs.point_id
                INNER JOIN trip_assign AS tras ON tras.id = tpoint.trip_assign_id
                INNER JOIN trip AS ta ON tras.trip = ta.trip_id
                LEFT JOIN shedule ON shedule.shedule_id = ta.shedule_id
                LEFT JOIN trip_route AS tr ON tr.id = ta.route
                LEFT JOIN fleet_type AS tp ON tp.id = tprs.type
                LEFT JOIN trip_price_ext AS trext ON trext.assign_id = tras.id AND trext.date = '$date' AND trext.type = tprs.type
                LEFT JOIN fleet_registration AS fr ON fr.id = tras.fleet_registration_id
                LEFT JOIN trip_location AS tl1 ON tl1.name = tpoint.dep_point
                LEFT JOIN trip_location AS tl2 ON tl2.name = tpoint.arr_point
                LEFT JOIN wil_city AS citydep ON tl1.city = citydep.id
                LEFT JOIN wil_city AS cityarr ON tl2.city = cityarr.id
                WHERE tras.status = 1
                $whereext 
            ")
        );

        return $query;
    }

    public function scopeSeatAvail($query, $trip_id_no, $date, $type){
        $query = DB::select(
            DB::raw("
                SELECT 
                tpc.ticket_number AS picked, 
                tpc.seat_number AS seat_number
                FROM tkt_booking AS tb 
                INNER JOIN tkt_passenger_pcs AS tpc ON tpc.booking_id = tb.id_no
                INNER JOIN trip AS ta ON ta.trip_id = tb.trip_id_no
                WHERE tpc.fleet_type = $type 
                AND tb.trip_id_no = $trip_id_no 
                AND tb.booking_date LIKE '$date%'
            ")
        );

        return $query;
    }
}
