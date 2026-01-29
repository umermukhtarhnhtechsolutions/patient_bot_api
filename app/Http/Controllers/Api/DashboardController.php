<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Throwable;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $allDoctor = User::where('role', 'doctor')->count();
            $doctorThisMonth = User::whereMonth('created_at', Carbon::now())->where('role', 'doctor')->count();
            return response()->json([
                'status' => true,
                'message' => "Data Found",
                'allDoctor' => $allDoctor ?? 0,
                'doctorThisMonth' => $doctorThisMonth ?? 0,
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
