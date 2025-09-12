<?php

namespace App\Http\Schemas;

/**
 * @OA\Schema(
 *     schema="Player",
 *     title="Player",
 *     description="Modelo de jogador do sistema",
 *     type="object",
 *     required={"id", "user_id", "name", "created_at", "updated_at"}
 * )
 */
class PlayerSchema
{
    /**
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     format="int64",
     *     description="ID único do jogador",
     *     example=1
     * )
     */
    public $id;

    /**
     * @OA\Property(
     *     property="user_id",
     *     type="integer",
     *     format="int64",
     *     description="ID do usuário vinculado",
     *     example=1
     * )
     */
    public $user_id;

    /**
     * @OA\Property(
     *     property="name",
     *     type="string",
     *     description="Nome do jogador",
     *     maxLength=255,
     *     example="João Silva"
     * )
     */
    public $name;

    /**
     * @OA\Property(
     *     property="image",
     *     type="string",
     *     description="URL da imagem do jogador",
     *     nullable=true,
     *     example="https://example.com/avatar.jpg"
     * )
     */
    public $image;

    /**
     * @OA\Property(
     *     property="nickname",
     *     type="string",
     *     description="Apelido do jogador",
     *     nullable=true,
     *     example="Joãozinho"
     * )
     */
    public $nickname;

    /**
     * @OA\Property(
     *     property="goals",
     *     type="integer",
     *     description="Total de gols marcados",
     *     minimum=0,
     *     example=15
     * )
     */
    public $goals;

    /**
     * @OA\Property(
     *     property="assists",
     *     type="integer",
     *     description="Total de assistências",
     *     minimum=0,
     *     example=8
     * )
     */
    public $assists;

    /**
     * @OA\Property(
     *     property="tackles",
     *     type="integer",
     *     description="Total de desarmes",
     *     minimum=0,
     *     example=25
     * )
     */
    public $tackles;

    /**
     * @OA\Property(
     *     property="mvps",
     *     type="integer",
     *     description="Total de MVPs",
     *     minimum=0,
     *     example=3
     * )
     */
    public $mvps;

    /**
     * @OA\Property(
     *     property="wins",
     *     type="integer",
     *     description="Total de vitórias",
     *     minimum=0,
     *     example=12
     * )
     */
    public $wins;

    /**
     * @OA\Property(
     *     property="matches",
     *     type="integer",
     *     description="Total de partidas jogadas",
     *     minimum=0,
     *     example=20
     * )
     */
    public $matches;

    /**
     * @OA\Property(
     *     property="average_rating",
     *     type="number",
     *     format="float",
     *     description="Nota média do jogador",
     *     minimum=0,
     *     maximum=10,
     *     example=7.85
     * )
     */
    public $average_rating;

    /**
     * @OA\Property(
     *     property="win_percentage",
     *     type="number",
     *     format="float",
     *     description="Porcentagem de vitórias (calculado automaticamente)",
     *     minimum=0,
     *     maximum=100,
     *     example=60.00
     * )
     */
    public $win_percentage;

    /**
     * @OA\Property(
     *     property="created_at",
     *     type="string",
     *     format="date-time",
     *     description="Data e hora de criação do jogador",
     *     example="2025-01-15T10:30:00Z"
     * )
     */
    public $created_at;

    /**
     * @OA\Property(
     *     property="updated_at",
     *     type="string",
     *     format="date-time",
     *     description="Data e hora da última atualização",
     *     example="2025-01-15T10:30:00Z"
     * )
     */
    public $updated_at;

    /**
     * @OA\Property(
     *     property="user",
     *     ref="#/components/schemas/User",
     *     description="Dados do usuário vinculado ao jogador",
     *     nullable=true
     * )
     */
    public $user;
}
