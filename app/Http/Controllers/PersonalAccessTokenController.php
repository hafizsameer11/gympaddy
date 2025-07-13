<?php

namespace App\Http\Controllers;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PersonalAccessTokenController extends Controller
{
    public function index()
    {
        return PersonalAccessToken::paginate(10);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tokenable_type' => 'required|string',
            'tokenable_id' => 'required|integer',
            'name' => 'required|string',
            'token' => 'required|string|unique:personal_access_tokens,token',
            'abilities' => 'nullable|array',
            'last_used_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ]);
        $data['abilities'] = $data['abilities'] ?? [];
        $token = PersonalAccessToken::create($data);
        return response()->json($token, 201);
    }

    public function show($id)
    {
        return PersonalAccessToken::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $token = PersonalAccessToken::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string',
            'abilities' => 'nullable|array',
            'last_used_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ]);
        $token->update($data);
        return response()->json($token);
    }

    public function destroy($id)
    {
        $token = PersonalAccessToken::findOrFail($id);
        $token->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function setfcmToken(Request $request){
        $userId=Auth::user()->id;
        Log::info('FCM TOKEN SET'.$request->fcmToken);
        $fcmToken=$request->fcmToken;
        $user=User::where('id',$userId)->first();
        $user->fcmToken=$fcmToken;
        $user->save();
        return response()->json(['status'=>'success','message'=>'fcm token set successfulky'],200);
        
    }
}
