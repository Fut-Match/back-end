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

    /**
     * Sorteia os times da partida
     * 
     * @OA\Post(
     *     path="/api/matches/{id}/shuffle-teams",
     *     tags={"Matches"},
     *     summary="Sorteia os times",
     *     description="Distribui aleatoriamente os jogadores em dois times (apenas o administrador pode fazer isso)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da partida",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Times sorteados com sucesso"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Apenas o administrador pode sortear os times"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Não é possível sortear os times no estado atual da partida"
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function shuffleTeams(Request $request, FootballMatch $match): JsonResponse
    {
        $user = $request->user();
        $player = $user->player;

        if (!$player || $match->admin_id !== $player->id) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas o administrador da partida pode sortear os times'
            ], 403);
        }

        if ($match->status !== 'waiting') {
            return response()->json([
                'success' => false,
                'message' => 'Só é possível sortear times antes da partida iniciar'
            ], 400);
        }

        $teams = $match->shuffleTeams();

        return response()->json([
            'success' => true,
            'data' => $teams,
            'message' => 'Times sorteados com sucesso'
        ]);
    }

    /**
     * Inicia a partida
     * 
     * @OA\Post(
     *     path="/api/matches/{id}/start",
     *     tags={"Matches"},
     *     summary="Inicia a partida",
     *     description="Inicia uma partida que está aguardando (apenas o administrador pode fazer isso)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da partida",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     * @OA\Response(
     *         response=200,
     *         description="Partida iniciada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Partida iniciada com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="status", type="string", example="in_progress"),
     *                 @OA\Property(property="started_at", type="string", format="date-time"),
     *                 @OA\Property(property="current_minute", type="integer", example=0),
     *                 @OA\Property(property="is_paused", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Apenas o administrador pode iniciar a partida"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Não é possível iniciar a partida no estado atual"
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function startMatch(Request $request, FootballMatch $match): JsonResponse
    {
        $user = $request->user();
        $player = $user->player;

        if (!$player || $match->admin_id !== $player->id) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas o administrador da partida pode iniciá-la'
            ], 403);
        }

        if (!$match->startMatch()) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível iniciar a partida no estado atual'
            ], 400);
        }

        $match->load(['teams.players.user', 'admin.user']);

        return response()->json([
            'success' => true,
            'data' => $match,
            'message' => 'Partida iniciada com sucesso'
        ]);
    }

    /**
     * Pausa/Resume a partida
     * 
     * @OA\Post(
     *     path="/api/matches/{id}/toggle-pause",
     *     tags={"Matches"},
     *     summary="Pausa/Resume a partida",
     *     description="Pausa ou resume uma partida em andamento (apenas o administrador pode fazer isso)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da partida",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estado da partida alterado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Apenas o administrador pode pausar/resumir a partida"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Não é possível pausar/resumir a partida no estado atual"
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function togglePause(Request $request, FootballMatch $match): JsonResponse
    {
        $user = $request->user();
        $player = $user->player;

        if (!$player || $match->admin_id !== $player->id) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas o administrador da partida pode pausar/resumir'
            ], 403);
        }

        if (!$match->togglePause()) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível pausar/resumir a partida no estado atual'
            ], 400);
        }

        $status = $match->is_paused ? 'pausada' : 'resumida';
        
        return response()->json([
            'success' => true,
            'data' => $match->fresh(),
            'message' => "Partida {$status} com sucesso"
        ]);
    }

    /**
     * Finaliza a partida
     * 
     * @OA\Post(
     *     path="/api/matches/{id}/finish",
     *     tags={"Matches"},
     *     summary="Finaliza a partida",
     *     description="Finaliza uma partida em andamento e atualiza as estatísticas dos jogadores (apenas o administrador pode fazer isso)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da partida",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     * @OA\Response(
     *         response=200,
     *         description="Partida finalizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Partida finalizada com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="status", type="string", example="finished"),
     *                 @OA\Property(property="finished_at", type="string", format="date-time"),
     *                 @OA\Property(property="winning_team_id", type="integer"),
     *                 @OA\Property(property="teams", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="events", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Apenas o administrador pode finalizar a partida"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Não é possível finalizar a partida no estado atual"
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function finishMatch(Request $request, FootballMatch $match): JsonResponse
    {
        $user = $request->user();
        $player = $user->player;

        if (!$player || $match->admin_id !== $player->id) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas o administrador da partida pode finalizá-la'
            ], 403);
        }

        if (!$match->finishMatch()) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível finalizar a partida no estado atual'
            ], 400);
        }

        $match->load(['teams.players.user', 'winningTeam', 'events.player.user']);

        return response()->json([
            'success' => true,
            'data' => $match,
            'message' => 'Partida finalizada com sucesso'
        ]);
    }

    /**
     * Adiciona um evento à partida
     * 
     * @OA\Post(
     *     path="/api/matches/{id}/events",
     *     tags={"Matches"},
     *     summary="Adiciona evento à partida",
     *     description="Adiciona um evento (gol, assistência, desarme, defesa) durante a partida",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da partida",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"player_id", "event_type"},
     *             @OA\Property(property="player_id", type="integer", example=1),
     *             @OA\Property(property="event_type", type="string", enum={"goal", "assist", "tackle", "defense"}),
     *             @OA\Property(property="minute", type="integer", example=25),
     *             @OA\Property(property="description", type="string", example="Gol de pênalti")
     *         )
     *     ),
     * @OA\Response(
     *         response=201,
     *         description="Evento adicionado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Evento adicionado com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="event_type", type="string", example="goal"),
     *                 @OA\Property(property="minute", type="integer", example=25),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="player", type="object"),
     *                 @OA\Property(property="team", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Apenas o administrador pode adicionar eventos"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao adicionar evento"
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function addEvent(Request $request, FootballMatch $match): JsonResponse
    {
        $user = $request->user();
        $player = $user->player;

        if (!$player || $match->admin_id !== $player->id) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas o administrador da partida pode adicionar eventos'
            ], 403);
        }

        if ($match->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Só é possível adicionar eventos durante a partida'
            ], 400);
        }

        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
            'event_type' => 'required|in:goal,assist,tackle,defense',
            'minute' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        $eventPlayer = Player::find($validated['player_id']);
        
        // Verifica se o jogador está participando da partida
        if (!$match->participants()->where('player_id', $eventPlayer->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'O jogador não está participando desta partida'
            ], 400);
        }

        $event = $match->addEvent(
            $eventPlayer,
            $validated['event_type'],
            $validated['minute'],
            $validated['description']
        );

        $event->load(['player.user', 'team']);

        return response()->json([
            'success' => true,
            'data' => $event,
            'message' => 'Evento adicionado com sucesso'
        ], 201);
    }
}
