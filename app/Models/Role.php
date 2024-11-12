<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    /**
     * Relación con el modelo User.
     * Un rol puede tener muchos usuarios.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
