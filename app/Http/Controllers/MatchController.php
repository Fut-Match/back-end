<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Matches",
 *     description="Operações relacionadas às partidas de futebol"
 * )
 */
class MatchController extends Controller
{
    /**
     * Lista todas as partidas disponíveis
     * 
     * @OA\Get(
     *     path="/api/matches",
     *     tags={"Matches"},
     *     summary="Lista todas as partidas",
     *     description="Retorna uma lista paginada de todas as partidas",
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por status da partida",
     *         required=false,
     *         @OA\Schema(type="string", enum={"waiting", "in_progress", "finished", "cancelled"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de partidas retornada com sucesso"
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = FootballMatch::with(['admin.user', 'participants.user'])
                             ->orderBy('match_date', 'desc')
                             ->orderBy('match_time', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $matches = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $matches,
            'message' => 'Lista de partidas recuperada com sucesso'
        ]);
    }

    /**
     * Cria uma nova partida
     * 
     * @OA\Post(
     *     path="/api/matches",
     *     tags={"Matches"},
     *     summary="Cria uma nova partida",
     *     description="Cria uma nova partida de futebol",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"match_date", "match_time", "location", "players_count", "end_mode"},
     *             @OA\Property(property="match_date", type="string", format="date", example="2025-09-15"),
     *             @OA\Property(property="match_time", type="string", format="time", example="18:00"),
     *             @OA\Property(property="location", type="string", example="Campo do Botafogo"),
     *             @OA\Property(property="players_count", type="string", enum={"3vs3", "5vs5", "6vs6"}),
     *             @OA\Property(property="end_mode", type="string", enum={"goals", "time", "both"}),
     *             @OA\Property(property="goal_limit", type="integer", example=5),
     *             @OA\Property(property="time_limit", type="integer", example=90)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Partida criada com sucesso"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dados de validação inválidos"
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'match_date' => 'required|date|after_or_equal:today',
            'match_time' => 'required|date_format:H:i',
            'location' => 'required|string|max:255',
            'players_count' => ['required', Rule::in(['3vs3', '5vs5', '6vs6'])],
            'end_mode' => ['required', Rule::in(['goals', 'time', 'both'])],
            'goal_limit' => 'nullable|integer|min:1|max:50',
            'time_limit' => 'nullable|integer|min:1|max:300',
        ]);

        // Validações condicionais
        if (in_array($validated['end_mode'], ['goals', 'both']) && empty($validated['goal_limit'])) {
            return response()->json([
                'success' => false,
                'message' => 'Limite de gols é obrigatório quando o modo de término inclui gols',
                'errors' => ['goal_limit' => ['Campo obrigatório para este modo de término']]
            ], 422);
        }

        if (in_array($validated['end_mode'], ['time', 'both']) && empty($validated['time_limit'])) {
            return response()->json([
                'success' => false,
                'message' => 'Limite de tempo é obrigatório quando o modo de término inclui tempo',
                'errors' => ['time_limit' => ['Campo obrigatório para este modo de término']]
            ], 422);
        }

        // Busca o player do usuário autenticado
        $user = $request->user();
        $player = $user->player;

        if (!$player) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não possui perfil de jogador'
            ], 400);
        }

        $validated['admin_id'] = $player->id;

        $match = FootballMatch::create($validated);
        $match->load(['admin.user', 'participants.user']);

        return response()->json([
            'success' => true,
            'data' => $match,
            'message' => 'Partida criada com sucesso'
        ], 201);
    }

    /**
     * Exibe detalhes de uma partida específica
     * 
     * @OA\Get(
     *     path="/api/matches/{id}",
     *     tags={"Matches"},
     *     summary="Exibe detalhes de uma partida",
     *     description="Retorna os detalhes de uma partida específica",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da partida",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes da partida retornados com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Partida não encontrada"
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(FootballMatch $match): JsonResponse
    {
        $match->load(['admin.user', 'participants.user']);

        return response()->json([
            'success' => true,
            'data' => $match,
            'message' => 'Detalhes da partida recuperados com sucesso'
        ]);
    }

    /**
     * Atualiza uma partida existente
     * 
     * @OA\Put(
     *     path="/api/matches/{id}",
     *     tags={"Matches"},
     *     summary="Atualiza uma partida",
     *     description="Atualiza os dados de uma partida (apenas o administrador pode atualizar)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da partida",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Partida atualizada com sucesso"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Não autorizado - apenas o administrador pode atualizar"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Partida não encontrada"
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(Request $request, FootballMatch $match): JsonResponse
    {
        $user = $request->user();
        $player = $user->player;

        if (!$player || $match->admin_id !== $player->id) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas o administrador da partida pode atualizá-la'
            ], 403);
        }

        $validated = $request->validate([
            'match_date' => 'sometimes|date|after_or_equal:today',
            'match_time' => 'sometimes|date_format:H:i',
            'location' => 'sometimes|string|max:255',
            'players_count' => ['sometimes', Rule::in(['3vs3', '5vs5', '6vs6'])],
            'end_mode' => ['sometimes', Rule::in(['goals', 'time', 'both'])],
            'goal_limit' => 'nullable|integer|min:1|max:50',
            'time_limit' => 'nullable|integer|min:1|max:300',
            'status' => ['sometimes', Rule::in(['waiting', 'in_progress', 'finished', 'cancelled'])],
        ]);

        $match->update($validated);
        $match->load(['admin.user', 'participants.user']);

        return response()->json([
            'success' => true,
            'data' => $match,
            'message' => 'Partida atualizada com sucesso'
        ]);
    }

    /**
     * Remove uma partida
     * 
     * @OA\Delete(
     *     path="/api/matches/{id}",
     *     tags={"Matches"},
     *     summary="Remove uma partida",
     *     description="Remove uma partida (apenas o administrador pode remover)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da partida",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Partida removida com sucesso"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Não autorizado - apenas o administrador pode remover"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Partida não encontrada"
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(Request $request, FootballMatch $match): JsonResponse
    {
        $user = $request->user();
        $player = $user->player;

        if (!$player || $match->admin_id !== $player->id) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas o administrador da partida pode removê-la'
            ], 403);
        }

        $match->delete();

        return response()->json([
            'success' => true,
            'message' => 'Partida removida com sucesso'
        ]);
    }

    /**
     * Participa de uma partida usando código
     * 
     * @OA\Post(
     *     path="/api/matches/join",
     *     tags={"Matches"},
     *     summary="Participa de uma partida",
     *     description="Permite que um jogador participe de uma partida usando o código",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code"},
     *             @OA\Property(property="code", type="string", example="ABC123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jogador adicionado à partida com sucesso"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao participar da partida"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Partida não encontrada"
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function join(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $match = FootballMatch::findByCode($validated['code']);

        if (!$match) {
            return response()->json([
                'success' => false,
                'message' => 'Partida não encontrada com o código informado'
            ], 404);
        }

        $user = $request->user();
        $player = $user->player;

        if (!$player) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não possui perfil de jogador'
            ], 400);
        }

        if (!$match->canJoin($player)) {
            $reasons = [];
            if ($match->isFull()) {
                $reasons[] = 'partida está cheia';
            }
            if ($match->status !== 'waiting') {
                $reasons[] = 'partida não está aguardando jogadores';
            }
            if ($match->participants()->where('player_id', $player->id)->exists()) {
                $reasons[] = 'você já está participando desta partida';
            }

            return response()->json([
                'success' => false,
                'message' => 'Não é possível participar da partida: ' . implode(', ', $reasons)
            ], 400);
        }

        $match->addParticipant($player);
        $match->load(['admin.user', 'participants.user']);

        return response()->json([
            'success' => true,
            'data' => $match,
            'message' => 'Você foi adicionado à partida com sucesso'
        ]);
    }

    /**
     * Sai de uma partida
     * 
     * @OA\Post(
     *     path="/api/matches/{id}/leave",
     *     tags={"Matches"},
     *     summary="Sai de uma partida",
     *     description="Permite que um jogador saia de uma partida",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da partida",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jogador removido da partida com sucesso"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao sair da partida"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Partida não encontrada"
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function leave(Request $request, FootballMatch $match): JsonResponse
    {
        $user = $request->user();
        $player = $user->player;

        if (!$player) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não possui perfil de jogador'
            ], 400);
        }

        if (!$match->participants()->where('player_id', $player->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não está participando desta partida'
            ], 400);
        }

        // Administrador não pode sair da própria partida
        if ($match->admin_id === $player->id) {
            return response()->json([
                'success' => false,
                'message' => 'O administrador não pode sair da partida. Cancele a partida se necessário.'
            ], 400);
        }

        $match->removeParticipant($player);
        $match->load(['admin.user', 'participants.user']);

        return response()->json([
            'success' => true,
            'data' => $match,
            'message' => 'Você saiu da partida com sucesso'
        ]);
    }
}
