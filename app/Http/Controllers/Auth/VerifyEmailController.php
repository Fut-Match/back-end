<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Email Verification",
 *     description="Operações de verificação de email"
 * )
 */
class VerifyEmailController extends Controller
{
    /**
     * Verificar email através do link enviado
     * 
     * @OA\Get(
     *     path="/email/verify/{id}/{hash}",
     *     tags={"Email Verification"},
     *     summary="Verifica email através do link",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Email verificado com sucesso"),
     *     @OA\Response(response="400", description="Link inválido ou expirado")
     * )
     */
    public function verify(EmailVerificationRequest $request)
    {
        // Verificar se o email já foi verificado
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email já foi verificado anteriormente'
            ], 200);
        }

        // Marcar email como verificado
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return response()->json([
            'success' => true,
            'message' => 'Email verificado com sucesso! Agora você pode fazer login.'
        ], 200);
    }
}
