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
            return $this->failNotFound('Data Not Found');
        } 

        $result = Trip::listBus($request);
        
        if (empty($result)) {
            return $this->sendError('Validation Error.', $validator->errors());       
        } 

        foreach ($result as $key => $value) {
            $checkSeat = Trip::seatAvail($value->trip_id_no, $request->date, $value->type);
            $value->seatPickedArr = $checkSeat; 
            $value->seatPicked = strval(COUNT($checkSeat)); 
            $value->seatAvail = intval($value->fleet_seats) - intval(COUNT($checkSeat)); 
            if ($value->seatAvail < 0) {
                $value->redunt = $value->seatAvail; 
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

        if ($fleetSeats[0]->layout == "2-2") {
            $separate = [2,6,10,14,18,22];

            foreach ($seatArray as $key => $value) {
                if (in_array(trim($value), $bookArray)) {
                    $avail = false;
                } else {
                    $avail = true;
                    // if ($booking_date > date("Y-m-d", strtotime('2022-11-06'))) {
                    //     $avail = false;
                    // }
                }

                if (trim($value) == 'X') {
                    $avail = false;
                    $value = '-';
                }

                $result['seats'][] = [
                    'id' => $key+1,
                    'name' => trim($value),
                    'isAvailable' => $avail,
                    'isSeat' => true,
                ];
    
                if (in_array($key+1, $separate)) {
                    $result['seats'][] = [
                        'id' => 00,
                        'name' => '-',
                        'isAvailable' => $avail,
                        'isSeat' => false,
                    ];
                }
            }

        } elseif ($fleetSeats[0]->layout == "1-1") {
            $separate = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20];
            $separate_2 = [1,3,5,7,9,11,13,14,15,17,19,21];

            foreach ($seatArray as $key => $value) {
                if (trim($value) != "") {
                    if (in_array(trim($value), $bookArray)) {
                        $avail = false;
                    } else {
                        $avail = true;
                        // if ($booking_date > date("Y-m-d", strtotime('2022-11-06'))) {
                        //     $avail = false;
                        // }
                    }

                    if (trim($value) == 'X') {
                        $result['seats'][] = [
                            'id' => 00,
                            'name' => '-',
                            'isAvailable' => $avail,
                            'isSeat' => false,
                        ];
                    } else {
                        $result['seats'][] = [
                            'id' => $key+1,
                            'name' => trim($value),
                            'isAvailable' => $avail,
                            'isSeat' => true,
                        ];
                    }
        
                    if (in_array($key+1, $separate)) {
                        $result['seats'][] = [
                            'id' => 00,
                            'name' => '-',
                            'isAvailable' => $avail,
                            'isSeat' => false,
                        ];
                    }
                    if (in_array($key+1, $separate_2)) {
                        $result['seats'][] = [
                            'id' => 00,
                            'name' => '-',
                            'isAvailable' => $avail,
                            'isSeat' => false,
                        ];
                    }
                }
            }
        } elseif ($fleetSeats[0]->layout == "1-1-1") {
            $separate = [1,2,4,5,7,8,10,11,13,14,16,17,19,20];
            $separate_2 = [3,6,12,15,18,21];

            foreach ($seatArray as $key => $value) {
                if (trim($value) != "") {
                    if (in_array(trim($value), $bookArray)) {
                        $avail = false;
                    } else {
                        $avail = true;
                        // if ($booking_date > date("Y-m-d", strtotime('2022-11-06'))) {
                        //     $avail = false;
                        // }
                    }

                    if (trim($value) == 'X') {
                        $result['seats'][] = [
                            'id' => 00,
                            'name' => '-',
                            'isAvailable' => $avail,
                            'isSeat' => false,
                        ];
                    } else {
                        $result['seats'][] = [
                            'id' => $key+1,
                            'name' => trim($value),
                            'isAvailable' => $avail,
                            'isSeat' => true,
                        ];
                    }
        
                    if (in_array($key+1, $separate)) {
                        $result['seats'][] = [
                            'id' => 00,
                            'name' => '-',
                            'isAvailable' => $avail,
                            'isSeat' => false,
                        ];
                    }
                }
            }
        } else {
            $separate = [];
        }

        return $this->sendResponse($result, 'Get seat bus successfully.');
    }
}
