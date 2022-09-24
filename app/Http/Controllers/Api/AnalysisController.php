<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AnalysisService;
use App\Services\DecileService;
use App\Services\RFMService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        $subQuery = Order::betweenDate($request->startDate, $request->endDate);

        if($request->type === 'perDay')
        {
            list($data, $labels, $totals) = AnalysisService::perDay($subQuery);
        }

        if($request->type === 'perMonth')
        {
            list($data, $labels, $totals) = AnalysisService::perMonth($subQuery);
        }

        if($request->type === 'perYear')
        {
            list($data, $labels, $totals) = AnalysisService::perYear($subQuery);
        }

        // で知る分析
        if($request->type === 'decile')
        {
            list($data, $labels, $totals) = DecileService::decile($subQuery);
        }

        if($request->type === 'rfm') 
        {
            list($data, $totals, $eachCount) = RFMService::rfm($subQuery, $request->rfmPrms);
            
            return response()->json([
                'data' => $data,
                'type' => $request->type,
                'eachCount' => $eachCount,
                'totals' => $totals,
            ], Response::HTTP_OK);
        }

        return response()->json([
            'data' => $data,
            'type' => $request->type,
            'labels' => $labels,
            'totals' => $totals,
        ], Response::HTTP_OK);
    }
}
