<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker()->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? response()->json(['message' => 'Le lien de réinitialisation du mot de passe a été envoyé par e-mail.'])
                    : response()->json(['email' => 'Nous ne pouvons pas trouver un utilisateur avec cette adresse e-mail.'], 400);
    }
}
