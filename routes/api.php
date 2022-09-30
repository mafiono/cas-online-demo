<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::get('/game_tunnel/in/{provider_slug} ', [App\Http\Controllers\GameTunnelAPI::class, 'in'])->name('callbackIn');
//Route::get('/game_tunnel/out/{provider_slug} ', [App\Http\Controllers\GameTunnelAPI::class, 'out'])->name('callbackOut');

// Booongo Mixed
//Route::any('/game_tunnel/mixed/booongo/{game_slug}/{device_type}/{token}/{mode}', [App\Http\Controllers\GameTunnelAPI::class, 'mixed'])->name('mixed');

Route::any('/game_tunnel/bgaming/{game_slug}/{random_id}/{token}', [App\Http\Controllers\GameTunnelAPI::class, 'bgamingMixed'])->name('bgamingMixed');
Route::any('/game_tunnel/bgaming/{game_slug}/{token}', [App\Http\Controllers\GameTunnelAPI::class, 'bgamingMixed'])->name('bgamingMixed');


Route::any('/gs2c/v3/gameService', [App\Http\Controllers\GameTunnelAPI::class, 'pragmaticplayMixed'])->name('pragmaticplayMixed');
Route::any('/gs2c/ge/v4/gameService', [App\Http\Controllers\GameTunnelAPI::class, 'pragmaticplayMixed'])->name('pragmaticplayMixed');
Route::any('/gs2c/saveSettings.do', [App\Http\Controllers\GameTunnelAPI::class, 'pragmaticplayMixed'])->name('pragmaticplayMixed');
Route::any('/gs2c/reloadBalance.do', [App\Http\Controllers\GameTunnelAPI::class, 'pragmaticplayMixed'])->name('pragmaticplayMixed');


// Need to add middleware group for internal VLAN requests preferably -- actually better so later can loadbalance the gamerouter in seperate instances
Route::get('/internal/gameRouter', [App\Http\Controllers\SlotmachineController::class, 'gameRouter'])->name('gameRouterInternal');

// Need to add external API middleware group for legit games to return callbacks or whatever, opens up to host the backend seperately also
// Route::get('/external/gameRouter', [App\Http\Controllers\SlotmachineController::class, 'gameRouter'])->name('gameRouterExternal');

Route::get('/data/gameslist', [App\Models\Gamelist::class, 'dataQueryGamelist'])->name('dataQueryGamelist');
Route::get('/data/gamesessions', [App\Models\GameSessions::class, 'dataQueryGameSessions'])->name('dataQueryGameSessions');
