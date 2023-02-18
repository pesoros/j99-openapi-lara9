<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Book extends Model
{
    public function scopeGetCheckSeat($query, $datas)
    {
        $trip_id_no = isset($datas['trip_id_no']) ? $datas['trip_id_no'] : '';
        $trip_route_id = isset($datas['trip_route_id']) ? $datas['trip_route_id'] : '';
        $booking_date = isset($datas['booking_date']) ? $datas['booking_date'] : '';
        $fleet_type = isset($datas['fleet_type']) ? $datas['fleet_type'] : '';
        $seat_number = isset($datas['seat_number']) ? $datas['seat_number'] : '';

        $query = DB::connection('mysql2')->table("tkt_passenger_pcs AS tpp")
            ->select('tpp.id')
            ->join("tkt_booking AS tb", "tb.id_no", "=", "tpp.booking_id")
            ->whereNull("tb.tkt_refund_id")
            ->where("tb.trip_id_no", $trip_id_no)
            ->where("tb.trip_route_id", $trip_route_id)
            ->where("tpp.seat_number", $seat_number)
            ->where("tpp.fleet_type", intval($fleet_type))
            ->where("tb.booking_date", "like", "$booking_date%")
            ->get();

        return $query;
    }

    public function scopeGetTruePrice($query, $date, $fleet_type, $pickup, $drop)
    {
        $query = DB::connection('mysql2')->table('trip_point AS tp')
            ->select(
                'tpr.price AS price',
                'trext.price AS price_ext'
            )
            ->join('trip_point_price AS tpr', 'tpr.point_id','=','tp.id')
            ->join('trip_assign AS ta', 'ta.id','=','tp.trip_assign_id')
            ->leftJoin('trip_price_ext AS trext', function($join) use ($date, $fleet_type)
            {
                $join->on('trext.assign_id', '=', 'ta.id')->where('trext.date', '=', $date)->where('trext.type', '=', $fleet_type);
            })
            ->where('tpr.type',$fleet_type)
            ->where('tp.dep_point',$pickup)
            ->where('tp.arr_point',$drop)
            ->get();
        return $query;
    }

    public function scopeGetTripRoute($query, $trip_route_id)
    {
        $query = DB::connection('mysql2')->table('trip_route')->select('*')->where('id', $trip_route_id)->get();

        return $query;
    }

    public function scopeCheckBooking($query, $fleetId)
    {
        $query = DB::connection('mysql2')->table("fleet_type")
            ->select(
                "total_seat", 
                "seat_numbers",
                "fleet_facilities"
            )
            ->where('id', $fleetId)
            ->get();

        return $query;
    }

    public function scopeCreateGroup($query, $data)
    {
        $save = DB::connection('mysql2')->table('ws_booking_history')
            ->insert($data);

        return $save;
    }

    public function scopeGetBookedSeat($query, $tripIdNo, $booking_date)
    {
        $query = DB::connection('mysql2')->table('tkt_booking AS tb')
            ->selectRaw("
                tb.trip_id_no,
                tb.seat_numbers AS booked_serial
            ")
            ->where('tb.trip_id_no', $tripIdNo)
            ->where('tb.booking_date', "$booking_date%")
            ->whereNull("tb.tkt_refund_id")
            ->get();

        return $query;
    }

    public function scopeCreateTktBooking($query, $data)
    {
        $save = DB::connection('mysql2')->table('tkt_booking')
            ->insert($data);

        return $save;
    }

    public function scopeCreateTicket($query, $data)
    {
        $save = DB::connection('mysql2')->table('tkt_passenger_pcs')
            ->insert($data);

        return $save;
    }

    public function scopeGetWsSetting($query, $id)
    {
        if ($id !== null) {
            $query = DB::connection('mysql2')->table('ws_setting')->where('id', $id)->get();
        } else {
            $query = DB::connection('mysql2')->table('ws_setting')->get();
        }

        return $query;
    }

    public function scopeCreateBooking($query, $data)
    {
        $save = DB::connection('mysql2')->table('tkt_booking_head')
            ->insert($data);

        return $save;
    }

    public function scopePaymentRegistration($query, $data)
    {
        $save = DB::connection('mysql2')->table('payment_registration')
            ->insert($data);

        return $save;
    }

    public function scopeSavePayment($query, $data)
    {
        $save = DB::connection('mysql2')->table('payment_receive')
            ->insert($data);

        return $save;
    }

    public function scopeUpdateStatusPayment($query, $booking_code, $status)
    {
        $data['payment_status'] = $status;
        $update = DB::connection('mysql2')->table('tkt_booking_head')
            ->where('booking_code',$booking_code)
            ->update($data);

        return $update;
    }

    public function scopeGetBooking($query, $booking_code)
    {
        $booking = DB::connection('mysql2')->table('tkt_booking_head')
            ->where('booking_code',$booking_code)
            ->first();

        return $booking;
    }

    public function scopeCreateCancel($query, $bookingCode, $reason)
	{	 
		$updata['payment_status'] = 2;
        $cancelData = [
            "booking_code" => $bookingCode,
            "reason" => $reason,
            "date" => NOW(),
        ];
        $update = DB::connection('mysql2')
            ->table('tkt_booking_head')
            ->where('booking_code', $bookingCode)
            ->update($updata);

        $savecancel = DB::connection('mysql2')
            ->table('op_cancel')
            ->insert($cancelData);
		
		$update = DB::connection('mysql2')
            ->table('tkt_booking')
            ->where('booking_code', $bookingCode)
            ->update(['tkt_refund_id' => '1']);

		return $savecancel;
	}
}
