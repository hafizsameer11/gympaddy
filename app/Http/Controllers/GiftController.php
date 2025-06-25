<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use App\Http\Requests\StoreGiftRequest;
use App\Http\Requests\UpdateGiftRequest;
use App\Services\GiftService;
use Illuminate\Auth\AuthenticationException;

class GiftController extends Controller
{
    protected GiftService $giftService;

    public function __construct(GiftService $giftService)
    {
        $this->giftService = $giftService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->giftService->index();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used for API
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGiftRequest $request)
    {
        return $this->giftService->store($request->user(), $request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Gift $gift)
    {
        return $this->giftService->show($gift);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Gift $gift)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGiftRequest $request, Gift $gift)
    {
        return $this->giftService->update($gift, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gift $gift)
    {
        return $this->giftService->destroy($gift);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Always return JSON for API requests
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
  