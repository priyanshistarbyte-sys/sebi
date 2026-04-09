<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'company_id','uploaded_by','name','original_name','path','mime','ext','size','is_image','is_pdf','period'
    ];

    protected $casts = [
        'is_image' => 'boolean',
        'is_pdf'   => 'boolean',
        'size'     => 'integer',
    ];

    public function url(): string {
        return \Storage::disk('public')->url($this->path);
    }

    public function company(){ return $this->belongsTo(Company::class); }
    public function user(){ return $this->belongsTo(User::class, 'uploaded_by'); }
}
