<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['name', 'currency_symbol'];

    public function users() {
        return $this->belongsToMany(\App\Models\User::class)->withTimestamps();
    }
}
