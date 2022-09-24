<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        $startDate = '2022-08-01';
        $endDate = '2022-08-31';

        // 購買ID毎にまとめる
        $subQuery = Order::betweenDate($startDate, $endDate)
            ->groupBy('id')
            ->selectRaw('id, customer_id, customer_name, 
            SUM(subtotal) as totalPerPurchase');

        $subQuery = DB::table($subQuery)
        ->groupBy('customer_id', 'customer_name')
        ->selectRaw('customer_id, customer_name, 
        sum(totalPerPurchase) as total')
        ->orderBy('total', 'desc');

        //dd($subQuery);

        $count = DB::table($subQuery)->count();
        $total = DB::table($subQuery)->selectRaw('sum(total) as total')->get();
        $total = $total[0]->total;

        $decile = ceil($count / 10); // 10文の1の件数

        $bindValues = [];
        $tempValue = 0;
        for($i = 1; $i <= 10; $i++) {
            array_push($bindValues, 1 + $tempValue);
            $tempValue += $decile;
            array_push($bindValues, 1 + $tempValue);
        }

        // dd($count, $decile, $bindValues);

        // DB::statement('set @row_num = 0;');
        // $subQuery = DB::table($subQuery)
        // ->selectRaw("
        // row_num,
        // customer_id,
        // customer_name,
        // total,
        // case
        //     when ? <= row_num and row_num < ? then 1
        //     when ? <= row_num and row_num < ? then 2
        //     when ? <= row_num and row_num < ? then 3
        //     when ? <= row_num and row_num < ? then 4
        //     when ? <= row_num and row_num < ? then 5
        //     when ? <= row_num and row_num < ? then 6
        //     when ? <= row_num and row_num < ? then 7
        //     when ? <= row_num and row_num < ? then 8
        //     when ? <= row_num and row_num < ? then 9
        //     when ? <= row_num and row_num < ? then 10
        // end as decile
        // ", $bindValues)->get();

        // dd($subQuery);

        // $subQuery = DB::table($subQuery)
        // ->groupBy('decile')
        // ->selectRaw('decile,
        // round(avg(total)) as average, sum(total) as totalPerGroup')->get();

        // dd($subQuery);

        return Inertia::render('Analysis');
    }
}
