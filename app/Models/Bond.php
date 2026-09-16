<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bond extends Model
{
    //

protected $fillable = [
    'stack_id',
    'bond_serial',
    'note',
    'bond_link',
    'date',
    'operation_name',
    'received_from',
    'car_number',
    'is_missing'
];
public function items() { return $this->hasMany(BondItem::class); }
public function stack() { return $this->belongsTo(Stack::class); }

}
