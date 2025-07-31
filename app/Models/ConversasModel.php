<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversasModel extends Model
{
    use HasFactory;

    protected $table = 'conversas';

    // Define any fillable attributes
    protected $fillable = [
        'user_id',
        'numero',
        'nome',
        'foto',
        'status'
    ];

}

