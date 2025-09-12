# Relacionamento entre User e Player

## Estrutura do Relacionamento

O sistema utiliza duas entidades principais para gerenciar usuários e jogadores:

- **User**: Representa o usuário autenticado no sistema. Contém informações gerais como nome, e-mail e senha.
- **Player**: Representa o jogador no contexto do domínio do futebol. Contém atributos específicos como gols, assistências, partidas, etc.

### Diagrama Simplificado
```plaintext
+---------+       1   tem   1       +---------+
|  User   | ----------------------> | Player  |
+---------+                        +---------+
| id      |                        | id      |
| name    |                        | user_id |
| email   |                        | name    |
| password|                        | goals   |
+---------+                        +---------+
```

---

## Quando Usar Cada Entidade

### User
- **Finalidade**: Gerenciar autenticação e informações gerais do usuário.
- **Exemplos de Uso**:
  - Login e registro.
  - Recuperação de senha.
  - Verificação de e-mail.

### Player
- **Finalidade**: Gerenciar dados específicos do jogador no contexto do futebol.
- **Exemplos de Uso**:
  - Criar e gerenciar partidas.
  - Exibir estatísticas do jogador (gols, assistências, etc.).
  - Calcular porcentagem de vitórias.

---

## Boas Práticas

1. **Separação de Responsabilidades**:
   - Use `User` para autenticação e permissões.
   - Use `Player` para ações relacionadas ao jogo.

2. **Facilite o Acesso**:
   - Utilize métodos no modelo `User` para acessar o `Player` facilmente:
     ```php
     $player = $user->player;
     ```

3. **Documentação e Consistência**:
   - Documente claramente no código quando usar `User` e quando usar `Player`.
   - Use nomes consistentes para variáveis e métodos (ex.: `$player` para jogadores e `$user` para usuários).

---

## Exemplo de Código

### Modelo `User`
```php
public function player(): HasOne
{
    return $this->hasOne(Player::class);
}
```

### Modelo `Player`
```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

### Criando uma Partida com `Player`
```php
$match = Match::create([
    'player1_id' => $player1->id,
    'player2_id' => $player2->id,
    'score_player1' => 3,
    'score_player2' => 2,
    'date' => now(),
]);
```

---

## Conclusão
Manter o relacionamento entre `User` e `Player` permite uma separação clara de responsabilidades e facilita a expansão do sistema no futuro. Certifique-se de seguir as boas práticas para evitar confusões e inconsistências no código.
