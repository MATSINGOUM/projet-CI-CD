<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'account_id',
        'type',
        'amount',
        'target_account_id',
    ];

    protected static function newFactory()
    {
        return TransactionFactory::new();
    }
}
