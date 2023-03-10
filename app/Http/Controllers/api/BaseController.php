<?php


namespace App\Http\Controllers\api;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;


class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message, $blocktime = false)
    {
        if ($blocktime == true) {
            $response = [
                'success' => true,
                'message' => $message,
                'blockTime'=> 60,
                'data'    => $result,
            ];
        } else {
            $response = [
                'success' => true,
                'message' => $message,
                'data'    => $result,
            ];
        }


        return response()->json($response, 200);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }
}