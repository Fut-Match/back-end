<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Players",
 *     description="Operações relacionadas aos jogadores"
 * )
 */
class PlayerController extends Controller
{
    /**
     * Lista todos os jogadores
     * 
     * @OA\Get(
     *     path="/api/players",
     *     tags={"Players"},
     *     summary="Lista jogadores",
     *     description="Retorna uma lista paginada de todos os jogadores",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Lista de jogadores retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Jogadores listados com sucesso")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $players = Player::with('user')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $players,
            'message' => 'Jogadores listados com sucesso'
        ]);
    }

    /**
     * Exibe um jogador específico
     * 
     * @OA\Get(
     *     path="/api/players/{id}",
     *     tags={"Players"},
     *     summary="Exibe um jogador",
     *     description="Retorna os detalhes de um jogador específico",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do jogador",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Dados do jogador retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Jogador encontrado com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Jogador não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Jogador não encontrado")
     *         )
     *     )
     * )
     */
    public function show(Player $player): JsonResponse
    {
        $player->load('user');

        return response()->json([
            'success' => true,
            'data' => $player,
            'message' => 'Jogador encontrado com sucesso'
        ]);
    }

    /**
     * Atualiza as informações de um jogador
     * 
     * @OA\Put(
     *     path="/api/players/{id}",
     *     tags={"Players"},
     *     summary="Atualiza um jogador",
     *     description="Atualiza as informações de um jogador específico",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do jogador",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="João Silva"),
     *             @OA\Property(property="nickname", type="string", example="João10"),
     *             @OA\Property(property="image", type="string", example="https://example.com/image.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Jogador atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Jogador atualizado com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Não autorizado a editar este jogador",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Você não tem permissão para editar este jogador")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Player $player): JsonResponse
    {
        // Verifica se o usuário autenticado é o dono do player
        if (Auth::user()->id !== $player->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para editar este jogador'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'nickname' => 'sometimes|string|max:255|nullable',
            'image' => 'sometimes|string|nullable',
        ]);

        $player->update($validated);

        return response()->json([
            'success' => true,
            'data' => $player->fresh(),
            'message' => 'Jogador atualizado com sucesso'
        ]);
    }

    /**
     * Retorna as estatísticas do jogador autenticado
     * 
     * @OA\Get(
     *     path="/api/players/me",
     *     tags={"Players"},
     *     summary="Meu perfil de jogador",
     *     description="Retorna os dados do jogador do usuário autenticado",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Dados do jogador retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Dados do jogador retornados com sucesso")
     *         )
     *     )
     * )
     */
    public function me(): JsonResponse
    {
        $player = Auth::user()->player;

        if (!$player) {
            return response()->json([
                'success' => false,
                'message' => 'Jogador não encontrado para o usuário autenticado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $player,
            'message' => 'Dados do jogador retornados com sucesso'
        ]);
    }
}
