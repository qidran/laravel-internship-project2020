<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordPostRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function change_page()
    {
        return view('auth.passwords.change');
    }

    public function change(ChangePasswordPostRequest $request)
    {
        $user = User::find(Auth::id());
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect('/change-password')->with('success', 'Password changed.');
    }

}
