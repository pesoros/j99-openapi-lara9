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
   
        return $this->sendResponse($result, 'Get province successfully.');
    }
}
