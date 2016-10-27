<?php

namespace App\Http\Controllers;

use App\Model\Batch;
use App\Model\VehicleRecord;
use Illuminate\Http\Request;

class Home extends Controller
{

    public function index(Request $request)
    {
        $early_threshold = $request->get('early', 180);
        $late_threshold = $request->get('late', 180);
        $times = Batch::current()->rollup($early_threshold, $late_threshold);

        return view('index')->with('view', $times);
    }

}
