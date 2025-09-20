<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'action',
        'subject',
        'description',
    ];

    // Relación: Un permiso puede pertenecer a muchos roles (many-to-many)
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
