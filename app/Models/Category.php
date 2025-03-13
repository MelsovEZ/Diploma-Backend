<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $fillable = ['name'];

    public $timestamps = true;

    public static array $protectedCategories = ['Trash', 'Road'];

    public function isProtected(): bool
    {
        return in_array($this->name, self::$protectedCategories);
    }
}

