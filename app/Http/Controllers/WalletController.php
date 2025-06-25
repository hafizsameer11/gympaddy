<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Http\Requests\StoreWalletRequest;
use App\Http\Requests\UpdateWalletRequest;
use App\Http\Requests\TopupWalletRequest;
use App\Http\Requests\WithdrawWalletRequest;
use App\Services\WalletService;

class WalletController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->walletService->index();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWalletRequest $request)
    {
        return $this->walletService->store($request->user(), $request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Wallet $wallet)
    {
        return $this->walletService->show($wallet);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWalletRequest $request, Wallet $wallet)
    {
        return $this->walletService->update($wallet, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wallet $wallet)
    {
        return $this->walletService->destroy($wallet);
    }

    /**
     * Top up the wallet balance.
     */
    public function topup(TopupWalletRequest $request)
    {
        return $this->walletService->topup($request->user(), $request->validated());
    }

    /**
     * Withdraw from the wallet balance.
     */
    public function withdraw(WithdrawWalletRequest $request)
    {
        return $this->walletService->withdraw($request->user(), $request->validated());
    }

    /**
     * Not used for API - form-based actions.
     */
    public function create() { /* Not used in API */ }

    public function edit(Wallet $wallet) { /* Not used in API */ }
}
