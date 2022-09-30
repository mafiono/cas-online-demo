<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use \Carbon\Carbon; 
use Spatie\QueryBuilder\QueryBuilder;

class Gamelist extends Model
{
    protected $table = 'gamelist';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'game_id',
        'fullName',
        'provider',
        'thumbnail',
        'isHot',
        'isNew',
        'tags',
        'open',
        'rtpDes',
        'category',
        'order_rating',
        'thumbnail_ext',
        'short_desc', 
        'funplay'
    ];

    protected $hidden = [
        'open',
        'api_extension',
        'api_origin_id',
        'api_extra'
    ];

    public static function dataQueryGamelist() {
        $data = QueryBuilder::for(Gamelist::class)->allowedFields(['game_id', 'fullName', 'funplay', 'open', 'thumbnail', 'isHot', 'isNew'])->allowedSorts('provider')->paginate()->appends(request()->query());

        return $data;
    }

    public static function cachedGamelist() {
        $gamelistResponse = Cache::get('cachedGamelist');

        if(env('APP_ENV' === 'local')) {
                Artisan::command('optimize:clear'); 
        }

        if(!$gamelistResponse) {
            $gamelistResponse = self::all();

            $gamelist = Cache::put('cachedGamelist', $gamelistResponse, 15); // in minutes
        }

        return $gamelistResponse;
    }

    public static function cachedIndividualGame($game_id) {
        $gamespecificCached = Cache::get('cachedIndividualGame'.$game_id);

        if(env('APP_ENV' === 'local')) { Artisan::command('optimize:clear'); }

        if(!$gamespecificCached) {
            $selectGame = self::cachedGamelist()->where('game_id', '=', $game_id)->first();

            if($selectGame) {
            $gamespecificCached = Cache::put('cachedIndividualGame'.$game_id, $selectGame, 30); // in minutes
            } else {
                return 'not found';
            }
        }
        return $gamespecificCached;
    }

}
