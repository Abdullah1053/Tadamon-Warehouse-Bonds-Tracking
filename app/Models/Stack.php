<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stack extends Model
{
    //

protected $fillable = ['stack_name', 'start_serial', 'end_serial'];
public function bonds() { return $this->hasMany(Bond::class); }}
