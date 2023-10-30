<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index()
    {
        $provices = DB::select("SELECT full_name,code FROM provinces");
        return $this->sendResponse('Lấy dữ liệu thành công', $provices);
    }

    public function getDistrictByProvince(Request $request)
    {
        $province_code = $request->input('province_code');
        if ($request->input('province_code')) {
            $district = DB::select("SELECT full_name,code FROM districts WHERE province_code = " . $province_code);
            return $this->sendResponse('Lấy dữ liệu thành công', $district);
        }
        return $this->sendError('Lỗi chưa truyền $province_code');
    }

    public function getWardsByDistrict(Request $request)
    {
        $province_code = $request->input('district_code');
        if ($request->input('district_code')) {
            $district = DB::select("SELECT full_name,code FROM wards WHERE district_code = " . $province_code);
            return $this->sendResponse('Lấy dữ liệu thành công', $district);
        }
        return $this->sendError('Lỗi chưa truyền district_code');
    }
}
