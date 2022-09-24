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
        $startDate = '2022-08-20';
        $endDate = '2022-08-31';


        // RFM分析
        $subQuery = Order::betweenDate($startDate, $endDate)
        ->groupBy('id')
        ->selectRaw('id, customer_id, customer_name, 
        SUM(subtotal) as totalPerPurchase, created_at');

        $subQuery = DB::table($subQuery)
        ->groupBy('customer_id', 'customer_name')
        ->selectRaw('customer_id, customer_name, max(created_at) as recentDate,
        datediff(now(), max(created_at)) as recency,
        count(customer_id) as frequency,
        sum(totalPerPurchase) as monetary');

        // dd($subQuery);

        $rfmPrms = [
            14, 28, 60, 90, 7, 5, 3, 2, 300000, 200000, 100000, 30000
        ];

        $subQuery = DB::table($subQuery)
        ->selectRaw('customer_id, customer_name,
        recentDate, recency, frequency, monetary,
        case
            when recency < ? then 5
            when recency < ? then 4
            when recency < ? then 3
            when recency < ? then 2
            else 1 end as r,
        case
            when ? <= frequency then 5
            when ? <= frequency then 4
            when ? <= frequency then 3
            when ? <= frequency then 2
            else 1 end as f,
        case
            when ? <= monetary then 5
            when ? <= monetary then 4
            when ? <= monetary then 3
            when ? <= monetary then 2
            else 1 end as m', $rfmPrms);
        
        // dd($subQuery);
        
        $total = DB::table($subQuery)->count();

        $rCount = DB::table($subQuery)
        ->rightJoin('ranks', 'ranks.rank', '=', 'r')
        ->groupBy('rank')
        ->selectRaw('rank as r, count(r)')
        ->orderBy('r', 'desc')
        ->pluck('count(r)');

        $fCount = DB::table($subQuery)
        ->rightJoin('ranks', 'ranks.rank', '=', 'f')
        ->groupBy('rank')
        ->selectRaw('rank as f, count(f)')
        ->orderBy('f', 'desc')
        ->pluck('count(f)');

        $mCount = DB::table($subQuery)
        ->rightJoin('ranks', 'ranks.rank', '=', 'm')
        ->groupBy('rank')
        ->selectRaw('rank as m, count(m)')
        ->orderBy('m', 'desc')
        ->pluck('count(m)');

        // dd($rCount, $fCount, $mCount, $total);

        $eachCount = [];
        $rank = 5;

        for($i = 0; $i < 5; $i++) {
            array_push($eachCount, [
                'rank' => $rank, 
                'r' => $rCount[$i],
                'f' => $fCount[$i],
                'm' => $mCount[$i],
            ]);
            $rank--;
        }
        
        // dd($rCount, $fCount, $mCount, $total, $eachCount);

        $data = DB::table($subQuery)
        ->rightJoin('ranks', 'ranks.rank', '=', 'r')
        ->groupBy('rank')
        ->selectRaw('concat("r_", rank) as rRank,
        count(case when f = 5 then 1 end ) as f_5,
        count(case when f = 4 then 1 end ) as f_4,
        count(case when f = 3 then 1 end ) as f_3,
        count(case when f = 2 then 1 end ) as f_2,
        count(case when f = 1 then 1 end ) as f_1')
        ->orderBy('rRank', 'desc')
        ->get();



       //  dd($data);

        // 購買ID毎にまとめる
        // $subQuery = Order::betweenDate($startDate, $endDate)
        //     ->groupBy('id')
        //     ->selectRaw('id, customer_id, customer_name, 
        //     SUM(subtotal) as totalPerPurchase');

        // $subQuery = DB::table($subQuery)
        // ->groupBy('customer_id', 'customer_name')
        // ->selectRaw('customer_id, customer_name, 
        // sum(totalPerPurchase) as total')
        // ->orderBy('total', 'desc');

        // //dd($subQuery);

        // $count = DB::table($subQuery)->count();
        // $total = DB::table($subQuery)->selectRaw('sum(total) as total')->get();
        // $total = $total[0]->total;

        // $decile = ceil($count / 10); // 10文の1の件数

        // $bindValues = [];
        // $tempValue = 0;
        // for($i = 1; $i <= 10; $i++) {
        //     array_push($bindValues, 1 + $tempValue);
        //     $tempValue += $decile;
        //     array_push($bindValues, 1 + $tempValue);
        // }

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
