<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\api\BaseController as BaseController;
use App\Models\Trip;

class TripController extends BaseController
{
    public function listbus(Request $request)
    {
        $dayforday = date("l", strtotime(!empty($request->date)?$request->date:date('Y-m-d')));
        $dayArray = [
            'Sunday' => '1'
            ,'Monday' => '2'
            ,'Tuesday' => '3'
            ,'Wednesday' => '4'
            ,'Thursday' => '5'
            ,'Friday' => '6'
            ,'Saturday' => '7'
        ];

        if (!$request->date) {
            return $this->sendError('Date must be filled');
        } 

        $result = Trip::listBus($request);
        
        if (empty($result)) {
            return $this->sendError('Data Not Found');       
        } 

        foreach ($result as $key => $value) {
            $checkSeat = Trip::seatAvail($value->trip_id_no, $request->date, $value->type);
            $value->seatPicked = strval(COUNT($checkSeat)); 
            $value->seatAvail = intval($value->fleet_seats) - intval(COUNT($checkSeat)); 
            if ($value->seatAvail < 0) {
                $value->seatAvail = 0;
            }
            $spday = explode(',', $value->sp_day);
            for ($i=0; $i < count($spday); $i++) { 
                if ($spday[$i] == $dayArray[$dayforday]) {
                    $value->price = strval($value->sp_price);
                    $i = count($spday);
                }
            }
            if ($value->price_ext !== null) {
                $value->price = strval($value->price + intval($value->price_ext));
            }

            if ($value->image != null) {
                $value->image = getenv('ADMIN_ENDPOINT').$value->image;
            } else {
                $value->image = base_url('assets/default_bus.jpeg');
            }
        }

        return $this->sendResponse($result, 'Get list bus successfully.');
    }

    public function seatlist(Request $request) {
        $bookedSeats = Trip::getBookedSeats($request->trip_id_no, $request->booking_date, $request->fleet_type_id);
        $bookStr = '';
        $alphabet = range('A', 'Z');
        foreach ($bookedSeats as $key => $value) {
            $bookStr .= $value->booked_serial;
        }
        $bookArray = explode(',', $bookStr);

        $fleetSeats = Trip::getfleetseats($request->fleet_type_id);
        if (empty($fleetSeats)) {
            return $this->failNotFound('Data Not Found');
        }
        $seatArray = explode(',', $fleetSeats[0]->seat_numbers);

        $result['seatsInfo'] = $fleetSeats[0];
        $result['seatsInfo']->picked = $bookArray;

        foreach ($seatArray as $key => $value) {
            if (in_array(trim($value), $bookArray)) {
                $avail = false;
            } else {
                $avail = true;
                // if ($booking_date > date("Y-m-d", strtotime('2022-11-06'))) {
                //     $avail = false;
                // }
            }

            if (trim($value) != 'X' && trim($value) != '') {
                $rowcolumn = str_split(trim($value));
                $result['seats'][] = [
                    'name' => trim($value),
                    'isAvailable' => $avail,
                    'isSeat' => true,
                    'row' => $rowcolumn[0],
                    'column' => array_search($rowcolumn[1], $alphabet) + 1,
                ];
            }            
        }

        return $this->sendResponse($result, 'Get seat bus successfully.');
    }
}
