<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

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
