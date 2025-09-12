<?php

namespace App\Http\Schemas;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="Modelo de usuário do sistema",
 *     type="object",
 *     required={"id", "name", "email", "created_at", "updated_at"}
 * )
 */
class UserSchema
{
    /**
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     format="int64",
     *     description="ID único do usuário",
     *     example=1
     * )
     */
    public $id;

    /**
     * @OA\Property(
     *     property="name",
     *     type="string",
     *     description="Nome completo do usuário",
     *     maxLength=255,
     *     example="João Silva"
     * )
     */
    public $name;

    /**
     * @OA\Property(
     *     property="email",
     *     type="string",
     *     format="email",
     *     description="Email do usuário",
     *     maxLength=255,
     *     example="joao@example.com"
     * )
     */
    public $email;

    /**
     * @OA\Property(
     *     property="email_verified_at",
     *     type="string",
     *     format="date-time",
     *     description="Data e hora da verificação do email",
     *     nullable=true,
     *     example="2025-01-15T10:30:00Z"
     * )
     */
    public $email_verified_at;

    /**
     * @OA\Property(
     *     property="created_at",
     *     type="string",
     *     format="date-time",
     *     description="Data e hora de criação do usuário",
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
     *     property="player",
     *     ref="#/components/schemas/Player",
     *     description="Dados do jogador vinculado ao usuário",
     *     nullable=true
     * )
     */
    public $player;
}
