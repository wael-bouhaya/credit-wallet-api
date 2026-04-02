<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function index()
    {
        return response()->json(['solde' => auth('api')->user()->solde]);
    }

    public function spend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'montant' => 'required|integer|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth('api')->user();

        if ($request->montant > $user->solde) {
            return response()->json(['error' => 'Solde insuffisant'], 422);
        }

        $user->solde -= $request->montant;
        $user->save();

        return response()->json(['solde' => $user->solde]);
    }
}