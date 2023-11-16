<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use mysql_xdevapi\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class CustomerController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('phone')) {
            $query->where('phone', $request->input('phone'));
        }

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $customers = $query->get();
        $total = $customers->count();

        return response()->json([
            'status' => true,
            'total' => $total,
            'data' => $customers]);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        // Định nghĩa quy tắc kiểm tra hợp lệ
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'nullable|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'quantity' => 'required|integer|min:1',
        ];
        // Tạo một bộ kiểm tra với quy tắc và thông báo tùy chỉnh
        $validator = \Validator::make($request->all(), $rules);
        // Kiểm tra xem dữ liệu có hợp lệ không
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()], 400);
        }
        $customers = Customer::create($request->all());
        if ($customers) {
            return response()->json(['message' => 'Customer created successfully',
                'data' => $customers], 201);
        } else {
            return response()->json(['message' => 'Lỗi hệ thống',
                'data' => $customers], 500);
        }

    }

    /**
     * @param Customer $customers
     * @return JsonResponse
     */
    public function show(Customer $customers): JsonResponse
    {
        $customers = Customer::find($customers->id);
        if ($customers) {
            return response()->json([
                'status' => true,

                'data' => $customers]);
        } else {
            return response()->json(['message' => 'Sản phẩm không tồn tại.'], 404);
        }
    }


    /**
     * @param Request $request
     * @param Customer $customers
     * @return JsonResponse
     */
    public function updateUser(Request $request): JsonResponse
    {

        if ($request['avatar'] && str_contains($request['avatar'], 'storage/temp')) {
            $image = basename($request['avatar']);
            $sourcePath = public_path('storage/temp/' . $image);
            $destinationPath = public_path('storage/images/');
            $newFilename = basename($sourcePath);
            $status = File::move($sourcePath, $destinationPath . $newFilename);
            if ($status) {
                $request['avatar'] = $newFilename;
                File::delete(public_path($sourcePath));
            }
        }
        if ($request['avatar'] && str_contains($request['avatar'], 'storage/images')) {
            $request['avatar'] = basename($request['avatar']);
        }
        $username = Auth::guard('api')->user()['username'];

        $customers = Customer::where('username', $username)->first();
        $customers->update($request->all());
        return $this->sendResponse($customers, 'Thay đổi thông tin thành công');
    }

    public function changeStatus($id, Request $request)
    {
        $customers = Customer::find($id);

        if (!$customers) {
            return response()->json(['message' => 'Sản phẩm có id = ' . $id . ' không tồn tại'], 404);
        }
        $customers->status = $request->input('status');
        $customers->save();
        return response()->json(['message' => 'Cập nhật trạng thái thành ' . $customers->status . ' của sản phẩm thành công'], 201);
    }

    public function changeDelete($id, Request $request)
    {
        $customers = Customer::find($id);

        if (!$customers) {
            return response()->json(['message' => 'Sản phẩm có id = ' . $id . ' không tồn tại'], 404);
        }
        $customers->delete_flag = $request->input('delete_flag');
        $customers->save();
        return response()->json(['message' => 'Cập nhật trạng thái delete_flag = ' . $customers->delete_flag . ' của sản phẩm thành công'], 201);
    }

    /**
     * @param Customer $customers
     * @return Application|ResponseFactory|Response
     */
    public function destroy(Customer $customers)
    {
        // Xóa danh mục
        $customers->delete();
        return response(null, 204);
    }

    public function getInfoUser(Request $request)
    {
        echo "Hello";
    }

}
