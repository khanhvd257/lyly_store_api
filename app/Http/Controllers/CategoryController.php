<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $query = Category::query();
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $category = $query->get();
        $category = $category->map(function ($category) {
            if ($category->image_name) {
                $category->image_url = url('storage/images/' . $category->image_name);
            }
            return $category;
        });
        $total = $category->count();
        return response()->json([
            'status' => true,
            'total' => $total,
            'data' => $category]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        if (!$request->has('name')) {
            return response()->json(['error' => 'Trường "name" là bắt buộc.'], 400); // 400 là mã lỗi "Bad Request"
        }
        if ($request['image_url']) {
            $image = basename($request['image_url']);
            $sourcePath = public_path('storage/temp/' . $image);
            $destinationPath = public_path('storage/images/');
            File::move($sourcePath, $destinationPath . $image);
            $request['image_url'] = $image;
            File::delete(public_path($sourcePath));
        } else {
            $request['image_url'] = null;
        }
        $data = [
            'image_name' => $request['image_url'],
            'name' => $request['name'],
            'description' => $request['description'],
            'status' => $request['status'],
        ];
        // Tạo danh mục mới
        $category = Category::create($data);
        return response()->json(
            [
                'data' => $category,
                'status' => true,
                'code' => 201
            ], 201);
    }

    /**
     * @param Category $category
     * @return JsonResponse
     */
    public function show(Category $category): JsonResponse
    {
        $category = Category::find($category);
        if (!$category) {
            return response()->json(['message' => 'Danh mục không tồn tại'], 404);
        }
        // Hiển thị danh mục cụ thể
        return response()->json(['data' => $category]);
    }

    public function changeStatus($id, Request $request)
    {
        $category = Category::where('id', $id)->where('delete_flag', 0)->orWhere('delete_flag', 1)->first();

        if (!$category) {
            return response()->json(['message' => 'danh muc không tồn tại'], 404);
        }

        $category->delete_flag = $request->input('delete_flag');
        $category->save();
        return response()->json(['message' => 'Cập nhật trạng thái sản phẩm thành công'], 201);
    }

    /**
     * @param Request $request
     * @param Category $category
     * @return JsonResponse
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        // Cập nhật danh mục
        $category->update($request->all());
        return response()->json(['data' => $category]);
    }

    /**
     * @param Category $category
     * @return Application|ResponseFactory|Response
     */
    public function destroy(Category $category)
    {
        // Xóa danh mục
        $category->delete();
        return response(null, 204);
    }
}
