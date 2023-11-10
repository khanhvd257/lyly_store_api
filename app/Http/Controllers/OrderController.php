<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderValidation;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
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
//        $orderDetail = OrderDetail::find($order['id']);
//        $product = Product::find($orderDetail['product_id']);
//        $product['quantity'] += $orderDetail['quantity'];
//        $product->save();
        // Đã thay thế bằng TRIGGER TRONG MYsql
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
        if ($request['selectIds']) { // ORDER NHIỀU 1 SẢN PHẨM
            DB::beginTransaction();
            try {
                $ids = $request['selectIds'];
                $cartItems = Cart::whereIn('id', $ids)->get();
                $total = $cartItems->count();
                if ($total == 0) {
                    return $this->sendError('Không có sản phẩm trong giỏ hàng');
                }
                $username = Auth::guard('api')->user()['username'];
                $request['username'] = $username;
                $newOrder = Order::create($request->all());
                $newOrder->save();
                foreach ($cartItems as $cart) {
                    $orderDetail = new OrderDetail();
                    $orderDetail->order_id = $newOrder->id; // Sử dụng ID của đơn hàng mới
                    $orderDetail->product_id = $cart->product_id;
                    $orderDetail->quantity = $cart->quantity;
                    $orderDetail->price = $cart->total;
                    $orderDetail->save();
                    $cart->delete();
                }
                DB::commit();
                return $this->sendResponse([], 'Đặt hàng thành công');

            } catch (Exception $e) {
                // Nếu có lỗi, rollback giao dịch và xử lý lỗi
                DB::rollback();
                $this->sendError('Đã xảy ra lỗi khi đặt hàng');
            }
        } else { // ORDER TRƯC TIẾP 1 SẢN PHẨM
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

    public function getOrderByUsername(Request $request)

    {
        $status = $request->query('status');
        $username = Auth::guard('api')->user()['username'];
        $query = Order::where('username', $username);
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }
        $orders = $query->with('orderDetails.product')->get();
        foreach ($orders as $order) {
            $totalPrice = $order->orderDetails->sum(function ($orderDetail) {
                return $orderDetail->price;
            });
            $order->total_price = $totalPrice;
        }
        return $this->sendResponse($orders, 'lấy dữ liệu thành công');
    }

}
