<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductCreate;
use App\Models\Product;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use mysql_xdevapi\Exception;

/**
 *
 */
class ProductController extends Controller
{

    /**
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category'));
        }

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $products = $query->get();
        $products = $products->map(function ($product) {
            if ($product->image_url) {
                $product->image_url = url('storage/images/' . $product->image_url);
            }
            return $product;
        });
        $total = $products->count();
        return response()->json([
            'status' => true,
            'total' => $total,
            'data' => $products]);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(ProductCreate $request)
    {
        $validated = $request->validated();
        if ($validated['image_url']) {
            $image = basename($validated['image_url']);
            $sourcePath = public_path('storage/temp/' . $image);
            $destinationPath = public_path('storage/images/');
            $newFilename = basename($sourcePath);
            File::move($sourcePath, $destinationPath . $newFilename);
            $validated['image_url'] = $newFilename;
            File::delete(public_path($sourcePath));
        } else {
            $validated['image_url'] = null;
        }

        $product = Product::create($validated);
        if ($product) {
            $product['image_url'] = url('storage/images/' . $product['image_url']);
            return response()->json(['message' => 'Product created successfully',
                'data' => $product], 201);
        } else {
            return response()->json(['message' => 'Lỗi hệ thống',
                'data' => $product], 500);
        }

    }

    /**
     * @param Product $product
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        $product = Product::find($product->id);
        if ($product) {
            return response()->json([
                'status' => true,

                'data' => $product]);
        } else {
            return response()->json(['message' => 'Sản phẩm không tồn tại.'], 404);
        }
    }


    /**
     * @param Request $request
     * @param Product $product
     * @return JsonResponse
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        // Cập nhật danh mục
        $product->update($request->all());
        return response()->json([
            'status' => true,
            'old' => [$request->all()],
            'data' => $product]);
    }

    public function changeStatus($id, Request $request)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Sản phẩm có id = ' . $id . ' không tồn tại'], 404);
        }
        $product->status = $request->input('status');
        $product->save();
        return response()->json(['message' => 'Cập nhật trạng thái thành ' . $product->status . ' của sản phẩm thành công'], 201);
    }

    public function changeDelete($id, Request $request)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Sản phẩm có id = ' . $id . ' không tồn tại'], 404);
        }
        $product->delete_flag = $request->input('delete_flag');
        $product->save();
        return response()->json(['message' => 'Cập nhật trạng thái delete_flag = ' . $product->delete_flag . ' của sản phẩm thành công'], 201);
    }

    /**
     * @param Product $product
     * @return Application|ResponseFactory|Response
     */
    public function destroy(Product $product)
    {
        // Xóa danh mục
        $product->delete();
        return response(null, 204);
    }


}
