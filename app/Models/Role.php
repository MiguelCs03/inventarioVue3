<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tu_inicio',
        'activo',
    ];

    // Relación: Un rol puede tener muchos usuarios
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Relación: Un rol puede tener muchos permisos (many-to-many)
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
