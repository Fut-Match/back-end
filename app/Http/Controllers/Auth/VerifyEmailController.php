<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

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
    public function verify(Request $request, $id, $hash)
    {
        // Buscar o usuário pelo ID
        $user = User::findOrFail($id);

        // Verificar se o hash está correto (validação de assinatura)
        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'success' => false,
                'message' => 'Link de verificação inválido'
            ], 400);
        }

        // Verificar se a URL está assinada corretamente
        if (! URL::hasValidSignature($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Link de verificação expirado ou inválido'
            ], 400);
        }

        // Verificar se o email já foi verificado
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email já foi verificado anteriormente'
            ], 200);
        }

        // Marcar email como verificado
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'success' => true,
            'message' => 'Email verificado com sucesso! Agora você pode fazer login.'
        ], 200);
    }
}
