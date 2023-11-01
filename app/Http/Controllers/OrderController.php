<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderValidation;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    const PENDING = 'Pending';
    const CONFIRMED = 'Confirmed';
    const DONE = 'Done';

    public function orderProduct(OrderValidation $request)
    {
        $username = Auth::guard('api')->user()['username'];
        $request['username'] = $username;
        $request['status'] = self::PENDING;
        $product = Product::find($request['product_id']);
        $checkStock = $product->isAvailableInStock($request['quantity']);
        if (!$checkStock) {
            return $this->sendError('Kho thiếu hàng');
        } else {
            $newOrder = Order::create($request->all());
            $data = [
                'order_id' => $newOrder['id'],
                'quantity' => $request['quantity'],
                'product_id' => $request['product_id'],
                'price' => $request['quantity'] * $product['price'],
            ];
            $order = OrderDetail::create($data);
            if ($newOrder && $order) {
                $cart = Cart::where('username', $username)->where('product_id', $request['product_id'])->first();
                if ($cart) $cart->delete();
                $data['order'] = $newOrder;
                $data['orderDetail'] = $order;
                return $this->sendResponse($data, 'Đặt hàng thành công');
            }
            return $this->sendError('Xảy ra lỗi khi đặt hàng');

        }
    }
}
