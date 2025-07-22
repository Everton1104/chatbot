<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MensagensModel extends Model
{
    use HasFactory;

    protected $table = 'mensagens';

    // Define any fillable attributes
    protected $fillable = [
        'conversa_id',
        'msg',
        'link',
        'tipo',
        'status'
    ];

}

