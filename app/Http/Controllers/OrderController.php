<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderValidation;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    const PENDING = 'Pending';
    const CONFIRMED = 'Confirmed';
    const DONE = 'Done';

    public function orderProduct(OrderValidation $request)
    {
        $username = Auth::guard('api')->user()['username'];
        $product = Product::find($request['product_id']);
        $checkStock = $product->isAvailableInStock();
        if (!$checkStock) {
            return $this->sendError('Kho thiếu hàng');
        } else {
            $newOrder = Order::created($username);
            $data = [
                'order_id' => $newOrder['id'],
                'quantity' => $request['quantity'],
                'product_id' => $request['product_id'],
                'price' => $request['quantity'] * $product['price'],
                'delivery_address' => $request['delivery_address'],
                'note' => $request['node']
            ];
            $order = OrderDetail::created($data);
            return $this->sendResponse($order);

        }
    }
}
