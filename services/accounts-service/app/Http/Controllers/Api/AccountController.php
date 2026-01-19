<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    // Tous les comptes d'un user
    public function index($user_id)
    {
        return BankAccount::where('user_id', $user_id)->get();
    }

    // Créer un compte
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'type' => 'required|in:courant,epargne',
        ]);

        return BankAccount::create([
            'user_id' => $request->user_id,
            'account_number' => 'ACC-' . strtoupper(Str::random(10)),
            'type' => $request->type,
            'balance' => 0,
        ]);
    }

    // Voir un compte précis
    public function show($id)
    {
        return BankAccount::findOrFail($id);
    }

    // Désactiver un compte
    public function deactivate($id)
    {
        $account = BankAccount::findOrFail($id);
        $account->update(['is_active' => false]);

        return $account;
    }
}
