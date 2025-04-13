<?php
namespace App\Models\City;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['name'];
    public $timestamps = false;
}
