<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presets extends Model
{
    use HasFactory;

    protected $table = 'presets';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'preset',
        'preset_desc',
        'preset_value',
        'created_at',
        'updated_at',
    ];
    

}
