<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'icon', 'route', 'section', 'is_active', 'order'
    ];

    public function submenus()
    {
        return $this->hasMany(Submenu::class);
    }
}
