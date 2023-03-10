<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\api\BaseController as BaseController;
use App\Models\MasterData;

class MasterDataController extends BaseController
{
    public function province(Request $request)
    {
        $result = MasterData::province();
   
        return $this->sendResponse($result, 'Get province successfully.');
    }

    public function city(Request $request, $province_id = null)
    {
        $result = MasterData::city($province_id);
   
        return $this->sendResponse($result, 'Get City successfully.');
    }

    public function resto()
    {
        $result = MasterData::getResto();
        return $this->sendResponse($result, 'Get Restaurant successfully.');
    }

    public function restoMenu(Request $request)
    {
        $resto_id = isset($request->resto_id) ? $request->resto_id : '';

        $result = MasterData::getRestoMenu($resto_id);

        foreach ($result as $key => $value) {
            if ($value->image != null) {
                $value->image = env('ADMIN_ENDPOINT').$value->image;
            } else {
                $value->image = env('assets/default_food.jpeg');
            }
        }

        return $this->sendResponse($result, 'Get Menu successfully.');
    }

    public function poolPair()
    {
        $result = MasterData::poolPair();
   
        return $this->sendResponse($result, 'Get Pool Pair successfully.');
    }
}
