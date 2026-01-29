<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreRequest;
use App\Http\Requests\Doctor\UpdateRequest;
use App\Http\Resources\User\AllUserResource;
use App\Models\User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DoctorController extends Controller
{
    /**
     * Display a doctor of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = User::where('role', 'doctor');
            if ($request->skip)
                $query->skip($request->skip);
            if ($request->take)
                $query->take($request->take);
            $doctor = $query->orderBy('id', 'DESC')->get();
            return response()->json([
                'status' => true,
                'message' => ($doctor->count()) . " doctor(s) found",
                'data' => AllUserResource::collection($doctor),
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
    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $password = $request->password;
            $inputs = $request->except('image', 'password');
            $inputs['password'] = Hash::make($request->password);
            $inputs['role'] = 'doctor';
            $inputs['is_active'] = true;
            if (!empty($request->image) && file_exists($request->image)) {
                $image = $request->image;
                $filename = "Profile-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                $image->storeAs('doctor', $filename, "public");
                $inputs['image'] = "doctor/" . $filename;
            }
            $doctor = User::create($inputs);
            Mail::send('mail.account', [
                'email' => $doctor->email,
                'full_name' => $request->first_name . ' ' . $request->last_name,
                'password' => $password
            ], function ($message) use ($doctor) {
                $message->to($doctor->email);
                $message->subject('Account Credentials');
                $message->priority(3);
            });
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Doctor has been successfully added",
            ]);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $doctor)
    {
        if (empty($doctor)) {
            return response()->json([
                'status' => false,
                'message' => "Doctor not found",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "Doctor has been successfully found",
            'doctor' => new AllUserResource($doctor),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, User $doctor)
    {
        if (empty($doctor)) {
            return response()->json([
                'status' => false,
                'message' => "Doctor not found",
            ], 404);
        }

        try {
            DB::beginTransaction();
            $inputs = $request->except('image');
            if (!empty($request->image) && file_exists($request->image)) {
                $image = $request->image;
                $filename = "Profile-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                $image->storeAs('doctor', $filename, "public");
                $inputs['image'] = "doctor/" . $filename;
            }
            $doctor->update($inputs);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Doctor has been successfully updated",
            ]);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $doctor)
    {
        if (empty($doctor)) {
            return response()->json([
                'status' => false,
                'message' => "Doctor not found",
            ], 404);
        }

        try {
            DB::beginTransaction();
            if (!empty($doctor->image) && file_exists(public_path('storage/' . $doctor->image)))
                unlink(public_path('storage/' . $doctor->image));
            $doctor->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Doctor has been deleted successfully",
            ]);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function statusChange($id)
    {
        try {
            DB::beginTransaction();

            $doctor = User::find($id);
            if (!$doctor) {
                throw new Error('Doctor not found');
            }

            if ($doctor->is_active == true) {
                $doctor->is_active = false;
            } else {
                $doctor->is_active = true;
            }

            if (!$doctor->save()) {
                throw new Error('Doctor status could not be changed');
            }

            $statusText = $doctor->is_active ? 'approved' : 'disapproved';

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
