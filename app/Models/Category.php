<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['company_id','type','name','is_default','on_dashboard','dashboard_period'];
    protected $casts = ['is_default' => 'boolean'];

    public function company() {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
