<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Operações de autenticação e verificação de email"
 * )
 */
class AuthController extends Controller
{
    /**
     * Registrar um novo usuário
     * 
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Registra novo usuário",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="João Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Usuário registrado com sucesso"),
     *     @OA\Response(response="422", description="Dados inválidos")
     * )
     */
    public function register(Request $request)
    {
        // Validação dos dados
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required' => 'O nome é obrigatório',
            'email.required' => 'O email é obrigatório',
            'email.unique' => 'Este email já está em uso',
            'password.required' => 'A senha é obrigatória',
            'password.confirmed' => 'A confirmação da senha não confere',
        ]);

        // Criar o usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Disparar evento de registro (que enviará o email de verificação)
        event(new Registered($user));

        return response()->json([
            'success' => true,
            'message' => 'Usuário registrado com sucesso. Verifique seu email para ativar a conta.',
            'data' => [
                'user' => $user->only(['id', 'name', 'email']),
                'email_verified' => false
            ]
        ], 201);
    }

    /**
     * Login do usuário
     * 
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Faz login do usuário",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Login realizado com sucesso"),
     *     @OA\Response(response="401", description="Credenciais inválidas"),
     *     @OA\Response(response="403", description="Email não verificado")
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'O email é obrigatório',
            'password.required' => 'A senha é obrigatória',
        ]);

        // Tentar autenticar
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        $user = Auth::user();

        // Verificar se o email foi verificado
        if (!$user->hasVerifiedEmail()) {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Email não verificado. Verifique seu email antes de fazer login.',
                'email_verified' => false
            ], 403);
        }

        // Criar token de acesso
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'data' => [
                'user' => $user->only(['id', 'name', 'email']),
                'token' => $token,
                'email_verified' => true
            ]
        ], 200);
    }

    /**
     * Logout do usuário
     * 
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Authentication"},
     *     summary="Faz logout do usuário",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response="200", description="Logout realizado com sucesso")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso'
        ], 200);
    }

    /**
     * Reenviar email de verificação
     * 
     * @OA\Post(
     *     path="/api/email/verification-notification",
     *     tags={"Authentication"},
     *     summary="Reenvia email de verificação",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="joao@example.com")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Email de verificação enviado"),
     *     @OA\Response(response="400", description="Email já verificado")
     * )
     */
    public function resendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'O email é obrigatório',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado'
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email já foi verificado'
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Email de verificação reenviado com sucesso'
        ], 200);
    }
}
