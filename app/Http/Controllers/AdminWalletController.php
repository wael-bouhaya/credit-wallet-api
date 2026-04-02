<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminWalletController extends Controller
{
    public function credit(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'montant' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->solde += $request->montant;
        $user->save();

        return response()->json(['solde' => $user->solde]);
    }

    public function debit(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'montant' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->montant > $user->solde) {
            return response()->json(['error' => 'Solde insuffisant pour ce débit'], 422);
        }

        $user->solde -= $request->montant;
        $user->save();

        return response()->json(['solde' => $user->solde]);
    }
}