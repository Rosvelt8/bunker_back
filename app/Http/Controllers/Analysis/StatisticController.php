<?php

namespace App\Http\Controllers\Analysis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Order;

class StatisticController extends Controller
{
    public function getLast7DaysSales()
    {
        $salesData = Order::where('status', 'booked')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, SUM(total_price) as total_sales')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        // Structurer les données sous la forme data: [jour1, jour2, ...]
        $formattedData = $salesData->map(function ($item) {
            return [
                'date' => $item->date,
                'total_sales' => $item->total_sales,
            ];
        });

        return response()->json([
            'data' => $formattedData->pluck('total_sales')
        ]);
    }

    public function getTodaySalesTotal()
    {
        $todaySales = DB::table('orders')
            ->whereDate('created_at', Carbon::today())
            ->sum('total_price');

        return response()->json([
            'totalSales' => $todaySales
        ]);
    }

    public function getTop5Products()
    {
        $topProducts = DB::table('order_products')
                        ->join('products', 'order_products.product_id', '=', 'products.id')
                        ->select('products.name', DB::raw('SUM(order_products.quantity) as sales'), DB::raw('SUM(products.price * order_products.quantity) as revenue'))
                        ->groupBy('products.name')
                        ->orderBy('sales', 'desc')
                        ->limit(5)
                        ->get();

        return response()->json([
            'topProducts' => $topProducts
        ]);
    }

    public function getLast12MonthsRevenue()
    {
        $monthlyRevenueData = DB::table('orders')
                                ->select(DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('SUM(total_price) as revenue'))
                                ->where('status', 'booked')
                                ->where('created_at', '>=', Carbon::now()->subMonths(12))
                                ->groupBy('year', 'month')
                                ->orderBy('year', 'asc')
                                ->orderBy('month', 'asc')
                                ->get();

        $monthlyRevenue = array_fill(0, 12, 0);

        foreach ($monthlyRevenueData as $data) {
            $index = Carbon::create($data->year, $data->month)->diffInMonths(Carbon::now()->startOfMonth());
            $monthlyRevenue[11 - $index] = $data->revenue;
        }



        return response()->json([
            'data' => $monthlyRevenue
        ]);
    }

    public function getDashboardMetrics()
    {
        $monthlyRevenue = DB::table('orders')
            ->where('status', 'booked')
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->sum('total_price');

        $pendingOrders = DB::table('orders')
            ->whereNot('status', 'booked')
            ->count();

        $weeklyProductsSold = DB::table('order_products')
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('quantity');

        $remainingPayments = DB::table('orders')
            ->whereNot('status', 'booked')
            ->sum(DB::raw('total_price - amount_paid'));

        $metrics = [
            'monthlyRevenue' => $monthlyRevenue,
            'pendingOrders' => $pendingOrders,
            'weeklyProductsSold' => $weeklyProductsSold,
            'remainingPayments' => $remainingPayments
        ];

        return response()->json($metrics);
    }


    
}
