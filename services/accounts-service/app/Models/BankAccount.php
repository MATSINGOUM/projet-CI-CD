<?php

namespace App\Models;

use Database\Factories\BankAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'account_number',
        'type',
        'balance',
        'status',
        'is_active'
    ];

    protected static function newFactory()
    {
        return BankAccountFactory::new();
    }
}
