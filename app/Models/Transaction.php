<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
  
    protected $fillable = [
        'company_id','date','account_id','type','amount','category_id','description','group_id','main_transaction_id','transaction_type','invoice_amount','tds_rate',
        'base','gst','gstLocked','usable','netRec','status','name','cashfit','bankfit','tds_on_gst_rate','tds_on_gst','transferPair','tds','dir','created_by'
    ];

    public function company(){ return $this->belongsTo(Company::class); }
    public function category(){ return $this->belongsTo(Category::class, 'category_id'); } // Income/Expense
    public function account(){ return $this->belongsTo(Category::class, 'account_id'); }   // Account
    public function main(){ return $this->belongsTo(self::class, 'main_transaction_id'); }
    public function mirrors(){ return $this->hasMany(self::class, 'main_transaction_id'); }  // children created off this one

    public function isTransfer(): bool{ return $this->main_transaction_id !== null || $this->mirrors()->exists(); }

    /** @return array{main: Transaction, counter: Transaction|null} */
    public function mainAndCounter(): array
    {
        if ($this->main_transaction_id) {
            // this is the counter
            return ['main' => $this->main ?? $this->load('main')->main, 'counter' => $this];
        }
        // this might be the main
        $counter = $this->mirrors()->first();
        return ['main' => $this, 'counter' => $counter];
    }
    public function fromCompany()
    {
        return $this->belongsTo(Company::class, 'from_company_id');
    }

    public function toCompany()
    {
        return $this->belongsTo(Company::class, 'to_company_id');
    }
}
