<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventaire extends Model
{
    protected $guarded = [];

    public function lignes()
    {
        return $this->hasMany(InventaireLigne::class)->orderBy('bien_code');
    }
}