<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\ProfileUpdateRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\LoginResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\User\AllUserResource;
use App\Models\User;
use Error;
use Throwable;

class AuthController extends Controller
{

    public function currentUser()
    {
        try {
            $user = auth()->user();
            $current_user = User::find($user->id);
            if (empty($current_user))
                throw new Error($current_user->role . ' not found');
            return response()->json(['status' => true, 'message' => $current_user->role . ' found', 'data' => new AllUserResource($user)]);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }

    public function loginProcess(LoginRequest $request)
    {
        try {
            $loginCredentials = [
                'email' => $request->email,
                'password' => $request->password,
                'role' => $request->role,
            ];
            if (auth()->attempt($loginCredentials)) {
                $user = auth()->user();
                return new LoginResource(['token' => $user->createToken($user->email)->accessToken, 'data' => $user]);
            }
            throw new Error('Invalid Credentials. Please try again.', 412);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }

    public function forgotProcess(ForgotPasswordRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = User::where('email', $request->email)->first();
            $otp = rand(1000, 9999);
            $token = DB::select("SELECT * FROM password_reset_tokens WHERE email = ?", [$user->email]);
            if (isset($token[0])) {
                DB::update('update password_reset_tokens set token = ? where email = ?', [$otp, $user->email]);
            } else {
                DB::insert("insert into password_reset_tokens (email, token) values (?, ?)", [$user->email, $otp]);
            }

            Mail::send('mail.reset-password', ['otp' => $otp, 'email' => $user->email], function ($message) use ($user) {
                $message->to($user->email, $user->name);
                $message->subject('Reset Password - Code');
                $message->priority(3);
            });
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "A code has been sent to the email you provided.",
            ]);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }

    public function resetPasswordProcess(ResetPasswordRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = User::where('email', $request->email)->first();
            if (empty($user)) throw new Error("User not found");
            if (Hash::check($request->password, $user->password)) throw new Error('Please use different from current password.');
            User::where('email', $request->email)->update(['password' => bcrypt($request->password)]);
            DB::delete('DELETE FROM password_reset_tokens WHERE email = ?', [$request->email]);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Password reset successfully.",
            ]);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }

    public function updateProfile(ProfileUpdateRequest $request)
    {
        $user = User::where('id', auth()->user()->id)->first();
        if (empty($user)) throw new Error('User not found');
            try {
                DB::beginTransaction();
                $inputs = $request->except('image', 'password');
                if(!empty($request->password)){
                    $user->show_password = $request->password;
                    $user->password = Hash::make($request->password);
                }
                if (!empty($request->image) && file_exists($request->image)) {
                    if (!empty($user->image) && file_exists(public_path('storage/' . $user->image))) unlink(public_path('storage/' . $user->image));
                    $image = $request->image;
                    $filename = "Profile-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                    $image->storeAs($user->role, $filename, "public");
                    $inputs['image'] = $user->role . "/" . $filename;
                }
                if (!$user->update($inputs)) throw new Error('Profile information updated failed.');
                DB::commit();
                return response()->json(['status' => true, 'message' => "Profile Successfully Updated", 'data' => new AllUserResource($user)]);
            } catch (Throwable $th) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
            }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();
            if (!Hash::check($request->current_password, $user->password))
                throw new Error('Your current password cannot match your current password.');
            if (Hash::check($request->password, $user->password))
                throw new Error('Your new password cannot match your current password. Please enter a different password.');
            User::where('email', $user->email)->update(['password' => bcrypt($request->password)]);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Password updated successfully.",
            ]);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }
}
