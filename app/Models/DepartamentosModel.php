<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartamentosModel extends Model
{
    use HasFactory;

    protected $table = 'users_departamentos';

    protected $fillable = [
        'descDepartamento',
        'status',
    ];
}

