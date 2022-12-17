<?php
   
namespace App\Http\Controllers\api;
   
use Illuminate\Http\Request;
use App\Http\Controllers\api\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
   
class HomeController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->sendResponse([], 'Juragan 99 API');
    }
}