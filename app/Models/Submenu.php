<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Submenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id', 'name', 'route', 'is_active', 'order'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
