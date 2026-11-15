<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mouvement extends Model
{
    protected $guarded = [];

    public function bien()
    {
        return $this->belongsTo(Bien::class);
    }

    public function origine()
    {
        return $this->belongsTo(Service::class, 'origine_id');
    }

    public function destination()
    {
        return $this->belongsTo(Service::class, 'destination_id');
    }
}