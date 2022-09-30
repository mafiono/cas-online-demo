<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gamelist;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use \Carbon\Carbon;
use Illuminate\Support\Str;

class SlotmachineController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Generate game for player
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function launcher(Request $request)
    {
        if(env('APP_ENV', 'local')) {
            Log::debug($request->fullUrl());
        }

        //Mode should be added (demo, currency etc.), currency should be in DOM of user
        $validateQueryData = $request->validate([
            'game_id' => ['required', 'max:35', 'min:3'],
            'provider' => ['optional', 'max:15'],
            'api_extension' => ['optional', 'max:10'],
        ]);

        if(!auth()->user()) {
            // ! Error user not logged in
        } else {
            /* User Retrieve & Balance */
            $playerID = auth()->user()->id;
            $balance = auth()->user()->balance('USD'); //can add multi currency
        }

        /* Game Select & Retrieve */
        $game_id = $request->game_id; 
        $selectGame = Gamelist::cachedGamelist()->where('game_id', '=', $game_id)->where('open', 1)->first();

        if(!$selectGame) {
            // ! Error game_id not found 
        }

        $strLowerProvider = strtolower($selectGame->provider);
        if($request->provider) {
            $strLowerProvider = strtolower($request->provider);
        }
        $arrayAvailableProviders = 'bgaming, whatever'; // should be operator specific to check if provider is enabled for operator

        if(!isset($strLowerProvider, $arrayAvailableProviders)) {
            // ! Error provider not available or found
        }



        $buildArray = array(
            'game_id' => $selectGame->game_id,
            'game_name' => $selectGame->fullName,
            'api_origin_id' => $selectGame->api_origin_id,
            'api_extension' => $selectGame->api_extension,
            'provider' => $strLowerProvider,
            'player' => auth()->user()->id,
            'currency' => 'USD', // should be in request
            'mode' => 'real', //should be in request demo/real money play
            'method' => 'gameRequestByPlayer',
        );
        if(env('APP_ENV', 'local')) {
            Log::notice($buildArray);
        }


        /* This router can be hosted externally (preferably) within a private network (VLAN) that is connected to the public API, this way also illegal games can be hard split (and thus accountabillity) */
        return Http::timeout(5)->get(env('APP_URL').'/api/internal/gameRouter', $buildArray);



        //If script below continues, means that the getGameURL failed, so we can implement error handling below in future

        //Assign a random error identifier so we can store multiple of above (this all needs to go in handling exceptions within app at some point, though cause we wanna test sentry so far leaving that)

        $errorcode = rand(10000, 9999999999);
        Log::critical('ERRORID: '.$errorcode.' - $getGameUrl response: '.$getGameUrl);
        Log::critical('ERRORID: '.$errorcode.' - $selectGame from gamelist: '.$selectGame);
        Log::critical('ERRORID: '.$errorcode.' - Initial request to SlotmachineController.php: '.$request);
        abort(400); // Error 400 bad request, error frontend can be edited in /resources/views/errors/*.blade.php
        die();

    }



    /**
     * Slotmachine router, possibly should be in a helper/config so there is more room to adapt & loadbalance on high volume
     * If having lot of provider integration this is handy to extend, as you want a normalized callback to operator easily
     * 
     */
    public function gameRouter(Request $request)
    {
        $fullContent = $request;
        $method = $fullContent->method;

        if($method === 'gameRequestByPlayer') {
            $provider = $fullContent->provider;

            // should add EXTRA options for example per game/id/game_type and most importantly per api_id, these should however have additional filters hence why need to be done by yourself

            if($provider === 'bgaming') {
                return view('launcher')->with('content', self::bgamingSessionStart($request)); 
            }
            if($provider === 'booongo') {
                return self::booongoSessionStart($request);
            }
            if($provider === 'playson') {
                return self::playsonSessionStart($request);
            }
            if($provider === 'pragmaticplay') {
                return self::pragmaticplaySessionStart($request);
            }



            Log::critical('Provider method not found, this should not happen as at launcher() function, unless unsupported provider was tried to launch this should be checked.');
            return false;

        }
        
        return false;

    }


    /**
     *  Pragmaticplay Sesssion Start (needs be refactored obvs)
     */
    public function pragmaticplaySessionStart(Request $request)
    {
        $fullContent = $request;

        //Check if existing internal session is available
        $getInternalSession = \App\Models\GameSessions::where('game_id', $fullContent->game_id)->where('player_id', $fullContent->player)->where('currency', $fullContent->currency)->where('created_at', '>', Carbon::now()->subMinutes(45))->first();
  
        $newSession = false;

        //Create new internal session to relate towards, we will invalidate it after 45 minutes, mainly is used for 'continued' play so for exmaple if player leaves in middle of a bonus round or whatever, to connect to same session to continue play
        if(!$getInternalSession) {
            $createInternalSession = \App\Models\GameSessions::create([
                'game_id' => $fullContent->game_id,
                'token_internal' => Str::uuid(),
                'token_original' => '0',
                'currency' => $fullContent->currency,
                'extra_meta' => $request->api_origin_id,
                'expired_bool' => 0,
                'player_id' => $fullContent->player,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $newSession = true;
            $getInternalSession = $createInternalSession;
        }


        if($newSession === true) {

        $compactSessionUrl = "https://demogamesfree.pragmaticplay.net/gs2c/openGame.do?gameSymbol=".$request->api_origin_id."&websiteUrl=https%3A%2F%2Fdemogamesfree.pragmaticplay.net&jurisdiction=99&lobby_url=https%3A%2F%2Fwww.pragmaticplay.com%2Fen%2F&lang=EN&cur=".$request->currency."";

        // Curling/loading in the session URL to server, ready to edit whatever to then display to user after //
        $ch = curl_init($compactSessionUrl);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch);
        $redirectURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        $launcherTest = Http::withOptions([
            'verify' => false,
        ])->get($redirectURL);

        $parts = parse_url($redirectURL);
        parse_str($parts['query'], $query);

            $createInternalSession->update([
                'token_original' => $query['mgckey']
            ]);


        } else {
            $redirectURL = 'https://demogamesfree.pragmaticplay.net/gs2c/html5Game.do?extGame=1&mgckey='.$getInternalSession->token_original.'&symbol='.$request->api_origin_id.'&jurisdictionID=99';
            Log::debug($redirectURL);
        $launcherTest = Http::withOptions([
            'verify' => false,
        ])->get($redirectURL);

        }



        $replaceAPItoOurs = str_replace('/operator_logos/',  '', $launcherTest);
        $replaceAPItoOurs = str_replace('"datapath":"https://demogamesfree.pragmaticplay.net/gs2c/common/games-html5/games/vs',  '"datapath":"'.env('APP_URL').'/static_pragmatic', $replaceAPItoOurs);
        //$replaceAPItoOurs = str_replace('"/gs2c',  '"/api/gs2c', $replaceAPItoOurs);
         $replaceAPItoOurs = str_replace('"https://demogamesfree.pragmaticplay.net/gs2c/ge/v4/gameService',  '"https://tester.tollgate.io/api/gs2c/ge/v4/gameService', $replaceAPItoOurs);



        $replaceAPItoOurs = str_replace('device.pragmaticplay.net',  'tester.tollgate.io', $replaceAPItoOurs);
        $replaceAPItoOurs = str_replace('demoMode":"1"',  'demoMode":"0"', $replaceAPItoOurs);

        $replaceAPItoOurs = str_replace('/gs2c/v3/gameService',  '/api/gs2c/v3/gameService', $replaceAPItoOurs);



        $finalLauncherContent = $replaceAPItoOurs;

        return view('launcher')->with('content', $finalLauncherContent);

    }


    /**
     *  Playson Sesssion Start (needs be refactored obvs)
     */
    public function playsonSessionStart(Request $request)
    {


        //Mapping to booongo (same API)
        return self::booongoSessionStart($request);
    }

    /**
     * Booongo Sesssion Start (needs be refactored obvs)
     */
    public function booongoSessionStart(Request $request)
    {
        // Will be trying diff method on this provisioning, in regards to 'balance modification' to hide within in there a simple socket/pusher //
        
        $booongo_apikey = 'hj1yPYivJmIX4X1I1Z57494re';

        $fullContent = $request;
        $ourGameID = $fullContent->game;
        $selectGameBng = \App\Models\Gamelist::where('game_id', $ourGameID)->first(); // this shld be cached individually (on short cache game id strings)        
        $gameName = $selectGameBng->fullName;

        $api_origin_id = $selectGameBng->api_origin_id;
        //This case merged orig id (numeric) and orig game_hash/id together, so split these:
        $explodeIdMerge = explode('++', $api_origin_id);
        $orig_id = $explodeIdMerge[0];
        $orig_hash_id = $explodeIdMerge[1];

        $lang = "en";
        $timestamp = time();
        $compactSessionUrl = "https://gate-stage.betsrv.com/op/tigergames-stage/game.html?wl=demo&token=testtoken'.$timestamp.&game=".$orig_id."&lang=".$lang."&sound=1&ts=".$timestamp."&title=".$gameName."&platform=desktop";

        // Curling/loading in the session URL to server, ready to edit whatever to then display to user after //
        $ch = curl_init($compactSessionUrl);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch);
        $redirectURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        $launcherTest = Http::withOptions([
            'verify' => false,
        ])->get($redirectURL);


        $hardEditGameContent = str_replace('box7-stage.betsrv.com/gate-stage1/gs/', env('APP_BOOONGO_MIXED_API'), $launcherTest);
        //$hardEditGameContent = str_replace('appStarted = false', 'appStarted = true', $hardEditGameContent);
        $hardEditGameContent = str_replace('firstDetected = false', 'firstDetected = true', $hardEditGameContent);

        $finalLauncherContent = $hardEditGameContent;

        return view('launcher')->with('content', $finalLauncherContent);
    }




    /**
     * Bgaming (needs be refactored obvs)
     */
    public function bgamingSessionStart(Request $request)
    {

        $fullContent = $request;


        //Check if existing internal session is available
        $getInternalSession = \App\Models\GameSessions::where('game_id', $fullContent->game_id)->where('player_id', $fullContent->player)->where('currency', $fullContent->currency)->where('created_at', '>', Carbon::now()->subMinutes(45))->first();
        
        // <!!!! TESTING SESSION, UNCOMMMENT ABOVE - self note !>>>>
        //$getInternalSession = NULL;

        $newSession = false;

        //Create new internal session to relate towards, we will invalidate it after 45 minutes, mainly is used for 'continued' play so for exmaple if player leaves in middle of a bonus round or whatever, to connect to same session to continue play
        if(!$getInternalSession) {
            $createInternalSession = \App\Models\GameSessions::create([
                'game_id' => $fullContent->game_id,
                'token_internal' => Str::uuid(),
                'token_original' => '0',
                'currency' => $fullContent->currency,
                'extra_meta' => 'n/a',
                'expired_bool' => 0,
                'player_id' => $fullContent->player,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $newSession = true;
            $getInternalSession = $createInternalSession;
        }


        if($newSession === true) {

            $url = 'https://bgaming-network.com/play/'.$fullContent->api_origin_id.'/FUN?server=demo';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $html = curl_exec($ch);
            $redirectURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);

            $redirectURL = rawurldecode($redirectURL);
            $url_components = parse_url($redirectURL);
            $play_token = str_replace('play_token=', '', $url_components['query']);

            $createInternalSession->update([
                'token_original' => $play_token
            ]);

        } else {
            $redirectURL = 'https://bgaming-network.com/games/'.$fullContent->api_origin_id.'/FUN?play_token='.$getInternalSession->token_original;
        }

        $curlingGame = Http::withOptions([
            'verify' => false,
        ])->get($redirectURL);

        // str_replace basically is backend version of your regular js append/change
        $currency = $fullContent->currency;
        $mode = $fullContent->mode;


        if($mode === 'real') {
            $replaceCurrency = str_replace('FUN', $currency, $curlingGame);
        }



        // Check the API middleman function in this controller (to be made - 18:31pm)
        $replaceAPItoOurs = str_replace('https://bgaming-network.com/api/', env('APP_BGAMING_API'), $curlingGame);

        // Remove existing analytics, you can also replace by your own newrelic ID
        $removeExistingAnalytics = str_replace('https://boost.bgaming-network.com/analytics.js', ' ', $replaceAPItoOurs);
        $removeExistingAnalytics = str_replace('https://www.googletagmanager.com/gtag/js?id=UA-98852510-1', '', $removeExistingAnalytics);
        $removeExistingAnalytics = str_replace('98852510', ' ', $removeExistingAnalytics);
        $removeExistingAnalytics = str_replace('sentry', ' ', $removeExistingAnalytics);
        $removeExistingAnalytics = str_replace('https://js-agent.newrelic.com/nr-1215.min.js', ' ', $removeExistingAnalytics);

        //$url = str_replace('resources_path":"https://cdn.bgaming-network.com/html/BonanzaBillion', 'resources_path":"'.env('APP_URL').'/static/prod/HappyBillions-2', $removeExistingAnalytics);
        //$url = str_replace('https://cdn.bgaming-network.com/html/BonanzaBillion/loader.js', env('APP_URL').'/static/prod/HappyBillions-2/loader.js', $url);
        //$url = str_replace('https://cdn.bgaming-network.com/html/BonanzaBillion/bundle.js', env('APP_URL').'/static/prod/HappyBillions-2/basic/v0.0.1/bundle.js', $url);
        $finalGameContent = $removeExistingAnalytics;

        return $finalGameContent;

    }

}