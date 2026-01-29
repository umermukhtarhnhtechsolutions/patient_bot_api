<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Appointment\AllAppointmentResource;
use App\Models\Appointment;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class AppointmentController extends Controller
{
    /**
     * Display a doctor of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $query = Appointment::query();
            if($user && $user->role == 'doctor')
                $query->where('doctor_phone_no', $user->phone_no);
            if ($request->skip)
                $query->skip($request->skip);
            if ($request->take)
                $query->take($request->take);
            $appointment = $query->orderBy('id', 'DESC')->get();
            return response()->json([
                'status' => true,
                'message' => ($appointment->count()) . " appointment(s) found",
                'data' => AllAppointmentResource::collection($appointment),
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        if (empty($appointment)) {
            return response()->json([
                'status' => false,
                'message' => "Appointment not found",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "Appointment has been successfully found",
            'data' => new AllAppointmentResource($appointment),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
    }

    public function statusChange(Request $request)
    {
        try {
            DB::beginTransaction();
            if(empty($request->id)){
                return response()->json([
                    'status' => false,
                    'message' => "Appointment Id not found",
                ],500);
            }
            $appointment = Appointment::find($request->id);
            if (!$appointment) {
                throw new Error('Appointment not found');
            }

            if ($appointment->is_active == true) {
                $appointment->is_active = false;
            } else {
                $appointment->is_active = true;
            }

            if (!$appointment->save()) {
                throw new Error('Appointment status could not be changed');
            }

            $statusText = $appointment->is_active ? 'approved' : 'disapproved';

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Successfully " . ucfirst($statusText),
            ]);

        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
