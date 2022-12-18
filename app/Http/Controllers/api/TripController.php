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
}
