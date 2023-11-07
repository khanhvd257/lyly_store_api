<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartValidation;
use App\Http\Requests\OrderValidation;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{


    /**
     *
     * API LẤY DANH SÁCH MUA HÀNG CHO NGƯỜI MUA
     * @return JsonResponse
     */
    public function getCartByUsername()
    {
        $username = Auth::guard('api')->user()['username'];
        $cartList = Cart::where('username', $username)->with('productDetail')->get();
        if ($cartList) return $this->sendResponse($cartList, 'Lấy danh sách giỏ hàng thành công');
        return $this->sendError('Không tìm thấy giỏ hàng');
    }

    /**
     *
     * API THÊM GIỎ HÀNG
     *
     * @param CartValidation $request
     * @return JsonResponse
     */
    public function addToCart(CartValidation $request): JsonResponse
    {
        // lấy ra username
        $username = Auth::guard('api')->user()['username'];
        $product = Product::find($request['product_id']);
        if (!$product) {
            return $this->sendError('Không tìm thấy sản phẩm với id = ' . $request['product_id']);
        }

        // kiểm tra sản phẩm đã có trong giỏ hàng chưa
        $cartExist = Cart::where('product_id', $request['product_id'])
            ->where('username', $username)
            ->first();

        if ($cartExist) {
            $totalQuantity = $cartExist['quantity'] + $request['quantity'];
            $checkTotal = $totalQuantity <= $product['quantity'];
            if (!$checkTotal) return $this->sendError('Vượt quá kho');
            else {
                $cartExist['quantity'] = $totalQuantity;
                $cartExist['total'] = $totalQuantity * $product['price'];
                $cartExist->save();
                return $this->sendResponse($cartExist, 'Đã tăng số lượng trong giỏ hàng thành công');
            }
        }

        //so sánh số lượng kho và số lưượng mua
        if ($product->quantity < $request['quantity']) return $this->sendError('Số lượng mua vượt quá số lượng kho');
        $data = [
            'username' => $username,
            'quantity' => $request['quantity'],
            'product_id' => $request['product_id'],
            'total' => $request['quantity'] * $product->price];
        $cart = Cart::create($data);
        return $this->sendResponse($cart, 'Thêm vào giỏ hàng thành công');
    }

    // Sửa một mục trong giỏ hàng
    public function updateCart(CartValidation $request)
    {
        $username = Auth::guard('api')->user()['username'];
        $cart = Cart::where('product_id', $request['product_id'])->where('username', $username)->first();
        $product = Product::find($request['product_id']);

        if ($request['quantity'] > $product['quantity']) return $this->sendError('Không đủ số lượng kho');
        $cart['quantity'] = $request['quantity'];
        $cart['total'] = $product['price'] * $request['quantity'];
        $cart->save();
        return $this->sendResponse($cart, 'Cập nhật giỏ hàng thành công');
    }

    // Xóa một mục khỏi giỏ hàng
    public function removeCart($id)
    {
        $cartItem = Cart::find($id);
        if ($cartItem) {
            $cartItem->delete();
            return $this->sendResponse([], 'Xóa đơn hàng thành công');
        } else {
            return $this->sendError('Không tìm thấy đơn hàng');
        }
    }

    // Chuyen tu 1 gio hang sang order
    public function changeToOrder(Request $request)
    {
        $username = Auth::guard('api')->user()['username'];
        $request['username'] = $username;
        $newOrder = Order::create($request->all());
        $newOrder->save();
        $ids = $request['selectIds'];
        foreach ($ids as $id) {
            $cart = Cart::find($id);
            // Tạo một mục đặt hàng chi tiết (OrderDetail)
            $orderDetail = new OrderDetail();
            $orderDetail->order_id = $newOrder->id; // Sử dụng ID của đơn hàng mới
            $orderDetail->product_id = $cart->product_id;
            $orderDetail->quantity = $cart->quantity;
            $orderDetail->price = $cart->total;
            $orderDetail->save();
            // Xóa sản phẩm đã chuyển sang đơn hàng khỏi bảng Cart
            $cart->delete();
        }
    }
}
