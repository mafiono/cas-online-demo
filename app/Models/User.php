<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     */
    protected $fillable = [
        'name',
        'email',
        'balance_usd',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /**
     * Return main balance with multi-currency available
     *
     */
    public function balance($currencyRequest)
    {
        //Validation might be needed, depending if you open balance to open api 
        if(!$currencyRequest) {
            return '0.00 (Error, no currency specified in request';
        }
        $user = auth()->user();
        $currency = strtolower($currencyRequest);
        if($user) {
            $selectUser = self::where('id', '=', $user->id)->first();
            if($selectUser) {
                $compactAbbrivation = 'balance_'.$currency;
                $balanceGet = $selectUser->$compactAbbrivation;
                if($balanceGet) {
                    // Balance decimal configuration should be taken from a currency cache
                    $floatValueString = floatval($balanceGet);
                    $decimalNormalize = number_format($floatValueString, 2, '.', ''); //for safety, make sure to normalize decimals to not get any php_precision errors
                    
                    // If making to grapi/graphql or whatever, probably want to make into json object below including the actual currency abbrevation & for example currency pricing/exchange rateV
                    return $decimalNormalize;
                }
                // Error, while user was found there was error trying to select specific native currency
                Log::emergency('Error trying to retrieve balance from user');
                return '0.00 (Error, while user was found there was error)';
            }
        } else {
            Log::warning('Non-authed user trying to retrieve balance.');
            return '0.00 (Error, user not found)';
        }
    }

}