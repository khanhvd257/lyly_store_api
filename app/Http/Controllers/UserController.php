<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category'));
        }

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $account = $query->get();
        $total = $account->count();

        return response()->json([
            'status'=>true,
            'total' => $total,
            'data' => $account]);
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
        $account = User::create($request->all());
        if ($account) {
            return response()->json(['message' => 'User created successfully',
                'data' => $account], 201);
        } else {
            return response()->json(['message' => 'Lỗi hệ thống',
                'data' => $account], 500);
        }

    }

    /**
     * @param User $account
     * @return JsonResponse
     */
    public function show(User $account): JsonResponse
    {
        $account = User::find($account->id);
        if ($account) {
            return response()->json([
                'status' => true,

                'data' => $account]);
        } else {
            return response()->json(['message' => 'Sản phẩm không tồn tại.'], 404);
        }
    }


    /**
     * @param Request $request
     * @param User $account
     * @return JsonResponse
     */
    public function update(Request $request, User $account): JsonResponse
    {
        // Cập nhật danh mục
        $account->update($request->all());
        return response()->json([
            'status' => true,
            'old' => [$request->all()],
            'data' => $account]);
    }

    public function changeStatus($id, Request $request)
    {
        $account = User::find($id);

        if (!$account) {
            return response()->json(['message' => 'Sản phẩm có id = ' . $id . ' không tồn tại'], 404);
        }
        $account->status = $request->input('status');
        $account->save();
        return response()->json(['message' => 'Cập nhật trạng thái thành ' . $account->status . ' của sản phẩm thành công'], 201);
    }

    public function changeDelete($id, Request $request)
    {
        $account = User::find($id);

        if (!$account) {
            return response()->json(['message' => 'Sản phẩm có id = ' . $id . ' không tồn tại'], 404);
        }
        $account->delete_flag = $request->input('delete_flag');
        $account->save();
        return response()->json(['message' => 'Cập nhật trạng thái delete_flag = ' . $account->delete_flag . ' của sản phẩm thành công'], 201);
    }

    /**
     * @param User $account
     * @return Application|ResponseFactory|Response
     */
    public function destroy(User $account)
    {
        // Xóa danh mục
        $account->delete();
        return response(null, 204);
    }
}
