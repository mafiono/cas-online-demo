<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\QueryBuilder;

class GameSessions extends Model
{
    use HasFactory;

    protected $table = 'gamesessions';
    protected $timestamp = true;
    protected $primaryKey = 'id';

    protected $fillable = [
        'token_internal',
        'player_id',
        'game_id',
        'currency',
        'extra_meta',
        'token_original',
        'expired_bool',
        'created_at',
        'updated_at',
    ];




    public static function dataQueryGameSessions() {
        $data = QueryBuilder::for(GameSessions::class)
            ->allowedFields(['token_internal', 'player_id', 'game_id', 'extra_meta'])
            ->where('player_id', auth()->user()->id)
            ->allowedSorts(['game_id', 'created_at'])
            ->paginate()
            ->appends(request()->query());

        return $data;
    }


}
