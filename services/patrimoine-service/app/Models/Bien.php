<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bien extends Model
{
    protected $table = 'biens';
    protected $guarded = [];

    public const TYPES = ['administratif', 'logement', 'entrepot', 'local_technique'];
    public const ETATS = ['bon', 'a_rehabiliter', 'degrade', 'hors_service'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function mouvements()
    {
        return $this->hasMany(Mouvement::class)->latest();
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class)->latest();
    }
}