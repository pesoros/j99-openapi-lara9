<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\api\BaseController as BaseController;
use App\Models\Book;

class BookController extends BaseController
{
    public function book(Request $request)
    {
        $timezone = Book::getWsSetting(1);
        date_default_timezone_set($timezone[0]->timezone);
        $dateNow = date('Y-m-d H:i:s');
        $bookingCode = $this->codeGenerate("B", 8);
        $total_price = 0;
        $total_seat = 0;
        $roundTrip = 0;

        // check doubleseat
        $checkSeatPergi = $this->checkSeat($request->pergi);
        if ($request->pulang) {
            $checkSeatPulang = $this->checkSeat($request->pulang);
        } else {
            $checkSeatPulang = 0;
        }

        $checkCount = $checkSeatPergi + $checkSeatPulang;
        if ($checkCount > 0) {
            return $this->sendError('Seat not available');
        }
        // check doubleseat

        $getPricePerSeat = Book::getTruePrice($request->pergi['booking_date'], $request->pergi['fleet_type_id'], $request->pergi['pickup_location'], $request->pergi['drop_location']);

        $extPrice = $getPricePerSeat[0]->price_ext ? $getPricePerSeat[0]->price_ext : 0;
        $setPergiPrice = $request->pergi;
        $setPergiPrice['pricePerSeat'] = $getPricePerSeat[0]->price + $extPrice;
        $request->merge([
            'pergi' => $setPergiPrice,
        ]);        
        $data['pergi'] = $this->setTicket($request->pergi, $bookingCode, $request->offer_code);
        $total_price = $data['pergi']['price'];
        unset($data['pergi']['price']);
        unset($data['pergi']['message']);
        $total_seat = count($request->pergi['seatPicked']);

        if ($request->pulang) {
            $roundTrip = 1;
            $total_seat = $total_seat + count($request->pulang['seatPicked']);
            $getPricePerSeat = Book::getTruePrice($request->pergi['booking_date'], $request->pulang['fleet_type_id'], $request->pulang['pickup_location'], $request->pulang['drop_location']);
            $setPulangPrice = $request->pulang;  
            $setPulangPrice['pricePerSeat'] = $getPricePerSeat[0]->price + $extPrice;
            $request->merge([
                'pulang' => $setPulangPrice,
            ]);        
            $data['pulang'] = $this->setTicket($request->pulang, $bookingCode, $request->offer_code);
            $total_price = $total_price + $data['pulang']['price'];
            unset($data['pulang']['price']);
            unset($data['pulang']['message']);
        } else {
            $data['pulang'] = '-';
        }

        $payment_method = 'partner';
        $payment_channel_code = '-';
        $paystatus = '0';


        $setBookingCode = Book::createBooking([
            'booker' => $request->booker_email,
            'booking_code' => $bookingCode,
            'round_trip' => $roundTrip,
            'total_price' => $total_price,
            'total_seat' => $total_seat,
            'payment_status' => $paystatus,
            'offer_code' => $request->offer_code,
            'created_at' => $dateNow,
            'agent' => $request->booker_id,
        ]);

        $setBookingCode = Book::paymentRegistration([
            'email' => $request->booker_email,
            'booking_code' => $bookingCode,
            'payment_method' => $payment_method,
            'payment_channel_code' => $payment_channel_code,
            'price' => $total_price,
            'payment_id' => '-',
            'va_number' => '-',
            'mobile_link' => '-',
            'dekstop_link' => '-',
            'created_at' => $dateNow,
        ]);

        $data['bookingCode'] = $bookingCode;
        $data['payment']['total_price'] = $total_price;
   
        return $this->sendResponse($data, 'Book successfully.');
    }

    public function setTicket($datas, $bookingCode, $offer_code)
    {
        $trip_id_no = isset($datas['trip_id_no']) ? $datas['trip_id_no'] : '';
        $trip_route_id = isset($datas['trip_route_id']) ? $datas['trip_route_id'] : '';
        $pickup_location = isset($datas['pickup_location']) ? $datas['pickup_location'] : '';
        $drop_location = isset($datas['drop_location']) ? $datas['drop_location'] : '';
        $pricePerSeat = isset($datas['pricePerSeat']) ? $datas['pricePerSeat'] : '';
        $booking_date = isset($datas['booking_date']) ? $datas['booking_date'] : '';
        $fleet_type = isset($datas['fleet_type_id']) ? $datas['fleet_type_id'] : '';
        $facilities = null;

        $seatPicked = $datas['seatPicked'];
        $seatCount = count($seatPicked);
        $seat_number = ',';
        foreach ($seatPicked as $key => $value) {
            if ($key > 0) {
                $seat_number .= ',';
            }
            $seat_number .= $value['seat'];
        }

        $adult_sts = $seatCount;
        $child_sts = 0;
        $special_sts = 0;
        $totl_inpt = intval($child_sts) + intval($adult_sts) + intval($special_sts);
        
        $price = intval($pricePerSeat) * intval($seatCount);

        /// Every Route Children and special seats info
        $rout_chsp_seat = Book::getTripRoute($trip_route_id);

        if ($seatCount == $totl_inpt) {
            #--------------------------------------
            $booking_date = $booking_date . ' ' . date('H:i:s');

            if ($offer_code != '') {
                $discount = $this->checkOffer(
                    $offer_code,
                    $trip_route_id,
                    date('Y-m-d', strtotime($booking_date))
                );
            } else {
                $discount = 0;
            }
            $passengerId = $this->codeGenerate("P", 12);
            $bookId = $this->codeGenerate("G", 12);

            #--------------------------------------

            $postData = [
                'booking_code' => $bookingCode,
                'id_no' => $bookId,
                'trip_id_no' => $trip_id_no,
                'tkt_passenger_id_no' => $passengerId,
                'trip_route_id' => $trip_route_id,
                'pickup_trip_location' => $pickup_location,
                'drop_trip_location' => $drop_location,
                'request_facilities' => $facilities,
                'price' => $price,
                'discount' => $discount,
                'adult' => $adult_sts,
                'child' => $child_sts,
                'special' => $special_sts,
                'total_seat' => $seatCount,
                'seat_numbers' => $seat_number,
                'offer_code' => $offer_code,
                'tkt_refund_id' => null,
                'agent_id' => null,
                'booking_date' => $booking_date,
                'date' => date('Y-m-d H:i:s'),
                'fleet_type' => intval($fleet_type),
                'status' => '0',
            ];

            #---------check seats--------
            $bookCheck = $this->checkBooking($trip_id_no, $fleet_type, $seat_number, $booking_date);
            if ($bookCheck) {

                if (Book::createGroup($postData)) {

                    foreach ($seatPicked as $key => $value) {
                        $ticketNumber = $this->codeGenerate("T", 8);
                        $ticketdata = [
                            'booking_id' => $bookId,
                            'ticket_number' => $ticketNumber,
                            'name' => $value['name'],
                            'fleet_type' => $fleet_type,
                            'seat_number' => $value['seat'],
                            'food' => $value['food'],
                            'baggage' => $value['baggage'],
                            'phone' => $value['phone'],
                        ];
                        $createTicket = Book::createTicket($ticketdata);
                    }

                    $data['status'] = true;
                    $data['message'] = 'save_successfully';

                    $postData['booking_type'] = 'Cash';
                    $postData['payment_status'] = 2;
                    unset($postData['offer_code']);
                    unset($postData['tkt_refund_id']);
                    unset($postData['status']);

                    $data['price'] = $price;

                    $insertdata = Book::createTktBooking($postData);
                } else {
                    $data['status'] = false;
                    $data['exception'] = 'please_try_again';
                }
            } else {
                $data['status'] = false;
                $data['exception'] = 'something_went_worng';
            }
        } else {
            $data['status'] = false;
            $data['exception'] = 'Please Check your seat';
        }

        return $data;
    }

    private function checkBooking($tripIdNo = null, $fleetId = null, $newSeats = null, $booking_date = null)
    {
        if ($tripIdNo == null || $fleetId == null || $newSeats == null) {
            return $tripIdNo . '-' . $fleetId . '-' . $newSeats . '-' . $booking_date;
        }

        //---------------fleet seats----------------
        $fleetSeats = Book::checkBooking($fleetId);

        $seatArray = array();
        if ($fleetSeats) {
            # code...
            $seatArray = array_map('trim', explode(',', $fleetSeats[0]->seat_numbers));
        } else {
            $seatArray = '';
        }
        //-----------------booked seats-------------------
        $bookedSeats = Book::getBookedSeat($tripIdNo, $booking_date);

        $bookArray = array();
        $bookStr = '';
        foreach ($bookedSeats as $key => $value) {
            $bookStr .= $value->booked_serial;
        }
        if ($bookStr !== null) {
            $bookArray = array_map('trim', explode(',', $bookStr));
        }

        //-----------------booked seats-------------------
        $newSeatArray = array();
        $newSeatArray = array_map('trim', explode(',', $newSeats));

        if (sizeof($newSeatArray) > 0) {

            foreach ($newSeatArray as $seat) {
                if (!empty($seat)) {
                    if (in_array($seat, $bookArray)) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function checkSeat($datas)
    {
        $trip_id_no = isset($datas['trip_id_no']) ? $datas['trip_id_no'] : '';
        $trip_route_id = isset($datas['trip_route_id']) ? $datas['trip_route_id'] : '';
        $booking_date = isset($datas['booking_date']) ? $datas['booking_date'] : '';
        $fleet_type = isset($datas['fleet_type_id']) ? $datas['fleet_type_id'] : '';
        $seatPicked = $datas['seatPicked'];
        $picked = 0;

        foreach ($seatPicked as $key => $value) {
            $datatoCheck = [
                'trip_id_no' => $trip_id_no,
                'trip_route_id' => $trip_route_id,
                'booking_date' => $booking_date,
                'fleet_type' => $fleet_type,
                'seat_number' => $value['seat'],
            ];
            $checkSeat = Book::getCheckSeat($datatoCheck);
            if (count($checkSeat) > 0) {
                $picked++;
            }
        }

        return $picked;
    }

    public function codeGenerate($head = 'J99', $length = 12)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $result = $head . '-' . $randomString;

        return $result;
    }
}
