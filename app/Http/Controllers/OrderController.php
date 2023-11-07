<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderValidation;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{


    /**
     * TRẠNG THÁI KHI NGƯỜI DÙNG ĐẶT HÀNG CHỜ XÁC NHẬN
     */
    const PENDING = 'Pending';
    /**
     * TRẠNG THÁI KHI NGƯƯỜI BÁN ĐÃ XÁC NHẬN
     */
    const CONFIRMED = 'Confirmed';

    /**
     *  HOÀN THÀNH ĐƠN HÀNG
     */
    const DONE = 'Done';

    /**
     *  HỦY ĐƠN HÀNG
     */
    const CANCEL = 'Cancel';

    public function confirmOrder($id)
    {
        $order = Order::find($id);
        if (!$order) return $this->sendError('Không thấy đơn hàng');
        if (!($order['status'] == self::PENDING)) return $this->sendError('Đơn hàng không hợp lệ do được xác nhận hoặc bị hủy');
        $order->update(['status' => self::CONFIRMED]);
        return $this->sendResponse($order, 'Xác nhận đơn hàng thành công');
    }

    /**
     * Nếu hủy thì phải số lượng kho
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrder($id)
    {
        $order = Order::find($id);
        if (!$order) return $this->sendError('Không thấy đơn hàng');
        if (($order['status'] == self::CANCEL) || $order['status'] == self::DONE)
            return $this->sendError('Đơn hàng đã bị hủy');
        $order->update(['status' => self::CANCEL]);
        $orderDetail = OrderDetail::find($order['id']);
        $product = Product::find($orderDetail['product_id']);
        $product['quantity'] += $orderDetail['quantity'];
        $product->save();
        return $this->sendResponse($order, 'Hủy đơn hàng thành công');

    }

    public function doneOrder($id)
    {
        $order = Order::find($id);
        if (!$order) return $this->sendError('Không thấy đơn hàng');
        if (!($order['status'] == self::CONFIRMED)) return $this->sendError('Đơn hàng không hợp lệ để hoàn thành');
        $order->update(['status' => self::DONE]);
        return $this->sendResponse($order, 'Đã hoàn thành đơn hàng thành công');

    }

    public function getDetailOrder($id)
    {
        $orders = OrderDetail::with('order')->with('product')->where('order_id', $id)->first();
        if (!$orders) return $this->sendError('Không tìm thấy đơn hàng');
        return $this->sendResponse($orders, 'Lấy thông tin thành công');


    }


    /**
     * Lấy danh sách tất cả order cho người bán
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllOrder()

    {
        $orders = OrderDetail::with('product')->with('order')->get();
        return $this->sendResponse($orders, 'Lấy dữ liệu thành công');
    }

    /**
     *  API ĐẶT HÀNG CHO NGƯỜI DÙNG
     * @param OrderValidation $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    public function getOrderByUser()
    {
        $username = Auth::guard('api')->user()['username'];

        $orders = DB::table('orders as o')
            ->join('order_details as od', 'o.id', '=', 'od.order_id')
            ->join('products as p', 'p.id', '=', 'od.product_id')
            ->where('username', $username)->orderByDesc('order_date')
            ->select(
                'od.id as order_id',
                'o.order_date',
                'o.status',
                'p.image_url',
                'p.name',
                'p.price as unitPrice',
                'od.quantity as num',
                'od.price as flowPrice'
            )
            ->get();
        foreach ($orders as $order) {
            $order->image_url = url('storage/images/' . $order->image_url); // Gán URL cho thuộc tính image_url
        }
        $data['orders'] = $orders;
        return $this->sendResponse($data, 'Lấy dữ liệu thành công');
    }
}
