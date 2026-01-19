<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // 💰 Recharger un compte
    public function deposit(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:1'
        ]);

        DB::transaction(function () use ($request) {
            $account = BankAccount::find($request->account_id);
            $account->increment('balance', $request->amount);

            Transaction::create([
                'account_id' => $account->id,
                'type' => 'deposit',
                'amount' => $request->amount,
            ]);
        });

        return response()->json(['message' => 'Account recharged']);
    }

    // 💸 Retirer de l'argent
    public function withdraw(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:1'
        ]);

        $account = BankAccount::find($request->account_id);

        if ($account->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        DB::transaction(function () use ($request, $account) {
            $account->decrement('balance', $request->amount);

            Transaction::create([
                'account_id' => $account->id,
                'type' => 'withdraw',
                'amount' => $request->amount,
            ]);
        });

        return response()->json(['message' => 'Withdrawal successful']);
    }

    // 🔄 Transfert entre comptes
    public function transfer(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|exists:bank_accounts,id',
            'to_account_id' => 'required|exists:bank_accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:1'
        ]);

        $from = BankAccount::find($request->from_account_id);
        $to   = BankAccount::find($request->to_account_id);

        if ($from->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        DB::transaction(function () use ($request, $from, $to) {
            $from->decrement('balance', $request->amount);
            $to->increment('balance', $request->amount);

            Transaction::create([
                'account_id' => $from->id,
                'type' => 'transfer',
                'amount' => $request->amount,
                'target_account_id' => $to->id,
            ]);
        });

        return response()->json(['message' => 'Transfer successful']);
    }

    // 📜 Historique d’un compte
    public function history($account_id)
    {
        return Transaction::where('account_id', $account_id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
