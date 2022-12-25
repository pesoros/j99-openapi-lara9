<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\api\BaseController as BaseController;
use App\Models\Check;

class CheckController extends BaseController
{
    public function cekBook($booking_code)
    {
        $result = Check::getBook($booking_code);
        if (!$result) {
            return $this->sendError('Data Not Found');
        }

        $getDetailBook = Check::detailBook($booking_code);

        if (isset($getDetailBook)) {
            $result->from = $getDetailBook->pickup_trip_location;
            $result->to = $getDetailBook->drop_trip_location;
        }
        
        $result->code_type = 'booking';
        $result->ticket = Check::getTicket($booking_code);

        if (empty($result)) {
            return $this->sendError('Data Not Found');
        } 

        return $this->sendResponse($result, 'Get ticket view successfully.');
    }

    public function cekTicket($ticket_code)
    {
        $result = Check::getTicket($ticket_code, 'ticket');
        if (empty($result)) {
            return $this->sendError('Data Not Found ..');
        } 

        $result->code_type = 'ticket';

        return $this->sendResponse($result, 'Get ticket view successfully.');
    }
}
