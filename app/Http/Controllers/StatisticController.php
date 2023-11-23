<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Ratings;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class StatisticController extends Controller
{
    public function statisticYear()
    {
        try {
            $results = DB::table('orders')
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->select(
                    DB::raw('MONTH(orders.order_date) AS month'),
                    DB::raw('SUM(order_details.price) AS total_amount'),
                )->where('orders.status', '=', 'Done')
                ->whereRaw('orders.order_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)')
                ->groupBy(DB::raw('MONTH(orders.order_date)'))
                ->get();
            return $this->sendResponse($results, 'Thanh Cong');
        } catch (\Exception $e) {
            return $this->sendError('Loi');
        }
    }

    public function statistics()
    {
        $newCustomers = Customer::whereRaw('created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)')->count();
        $totalProductsSold = DB::table('orders')
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->where('orders.status', '=', 'Done')
            ->whereRaw('orders.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)')
            ->sum('quantity');
        $totalOrders = Order::whereRaw('created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)')->count();
        $totalRatings = Ratings::whereRaw('created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)')->count();
        $data = [
            'new_customers_last_month' => $newCustomers,
            'total_products_sold' => $totalProductsSold,
            'total_orders' => $totalOrders,
            'total_ratings' => $totalRatings
        ];
        return $this->sendResponse($data, 'Lấy thông tin thống kê thành công');
    }

    public function getDetailProductIn12Month(Request $request)
    {
        $product = Product::find($request['product_id']);
        if (!$product) {
            return $this->sendError('Không có sản phẩm');
        }
        $monthlySales = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlySales[$i] = 0;
        }
        $results = DB::table('orders')
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->select(
                DB::raw('MONTH(orders.order_date) AS month'),
                DB::raw('SUM(order_details.quantity) AS total_sold'))
            ->where('orders.status', '=', 'Done')
            ->where('order_details.product_id', '=', $product['id'])
            ->whereRaw('orders.order_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)')
            ->groupBy(DB::raw('MONTH(orders.order_date)'))
            ->get();
        foreach ($results as $result) {
            $month = $result->month;
            $totalSold = $result->total_sold;
            $monthlySales[$month] = $totalSold;
        }
        $data = [
            'product_name' => $product['name'],
            'product_overview' => $monthlySales,
        ];
        return $this->sendResponse($data, 'Lấy thông tin thống kê thành công');
    }


    public function getRevenueByMonth(Request $request)
    {
        $month = Carbon::now()->month;
        if ($request['month']) {
            $month = $request['month'];
        }
        $totalProductsSold = DB::table('orders')
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->where('orders.status', '=', 'Done')
            ->whereRaw('MONTH(orders.order_date) = ?', [$month])
            ->sum('order_details.price');
        $data['revenue'] = $totalProductsSold;
        $data['month'] = $month;
        return $this->sendResponse($data, 'Lấy doanh thu thành công tháng ' . $month);

    }

    public function getorderDone()
    {
        $orders = Order::query();
        $orders->where('status', 'Done');
        $orders = $orders->with('orderDetails')->get();
        foreach ($orders as $order) {
            $totalPrice = $order->orderDetails->sum(function ($orderDetail) {
                return $orderDetail->price;
            });
            $order->total_price = $totalPrice;
        }
        return $this->sendResponse($orders, 'Thanh cong');
    }

}
