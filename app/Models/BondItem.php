<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BondItem extends Model
{
    protected $fillable = ['bond_id', 'item_description', 'quantity'];

    public function bond() { 
        return $this->belongsTo(Bond::class); 
    }
}