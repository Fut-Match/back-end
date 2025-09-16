<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="Operações relacionadas aos usuários do sistema"
 * )
 */
class UserController extends Controller
{
    /**
     * Lista todos os usuários
     * 
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Lista todos os usuários",
     *     description="Retorna uma lista paginada de todos os usuários do sistema",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Itens por página",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuários retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usuários listados com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/User")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autorizado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $perPage = min(max($perPage, 1), 100); // Limita entre 1 e 100

        $users = User::with('player')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Usuários listados com sucesso',
            'data' => $users
        ]);
    }

    /**
     * Exibe um usuário específico
     * 
     * @OA\Get(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Exibe um usuário específico",
     *     description="Retorna os dados de um usuário específico pelo ID",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID do usuário",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário encontrado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usuário encontrado com sucesso"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Usuário não encontrado"),
     *     @OA\Response(response=401, description="Não autorizado")
     * )
     */
    public function show(User $user): JsonResponse
    {
        $user->load('player');
        
        return response()->json([
            'success' => true,
            'message' => 'Usuário encontrado com sucesso',
            'data' => $user
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());
        $user->load('player');

        return response()->json([
            'success' => true,
            'message' => 'Usuário criado com sucesso',
            'data' => $user
        ], 201);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());
        $user->load('player');

        return response()->json([
            'success' => true,
            'message' => 'Usuário atualizado com sucesso',
            'data' => $user
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        // O player será automaticamente removido devido ao cascade na foreign key
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuário removido com sucesso'
        ]);
    }

    /**
     * Retorna dados do usuário autenticado
     * 
     * @OA\Get(
     *     path="/api/users/me",
     *     tags={"Users"},
     *     summary="Dados do usuário autenticado",
     *     description="Retorna os dados completos do usuário atualmente autenticado",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dados do usuário retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Dados do usuário autenticado"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autorizado")
     * )
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('player');

        return response()->json([
            'success' => true,
            'message' => 'Dados do usuário autenticado',
            'data' => $user
        ]);
    }
}
