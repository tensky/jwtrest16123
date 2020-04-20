<?php

namespace App\Http\Controllers;

use App\SocialMedia;
use App\User;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{   
    protected $header = [
        "alg" => "HS256",
        "typ" =>"JWT",
    ];
    
    public function registrasi(Request $request){

        $this->validate($request, [
            'nama' => 'required',
            'password' => 'required',
            'email' => 'required|email|unique:users'
        ]);

        $hashedPassword = password_hash($request->password, CRYPT_BLOWFISH);

        $user = User::create([
            'nama'=>$request->nama,
            'password'=>$hashedPassword,
            'email'=>$request->email
        ]);

        $payload = [
            "user_id" => $user->id,
            "iat" => time(),
        ];

        $encodedHeader = base64_encode(json_encode($this->header));
        $encodedPayload =  base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $encodedHeader . "." . $encodedPayload, env('SECRET'));
        $encodedSignature = base64_encode($signature);
        $token = $encodedHeader . "." . $encodedPayload . "." . $encodedSignature;
        return json_encode(['token'=>$token]);
    }

    public function updateData(Request $request){
        $token = explode(' ', $request->header('Authorization'));
        $id = $this->validateJWT($token[1]);

        if(!$id) return json_encode(['status'=>'invalid_token']);

        $this->validate($request, [
            'nama' => 'string',
            'email' => 'email|unique:users'
        ]);

        $user = User::find($id);

        if(!$user) return json_encode(['status'=>'invalid_user']);

        if($request->nama) $user->nama = $request->nama;
        if($request->email) $user->email = $request->email;
        $user->save();

        return json_encode(['status'=>'ok']);
    }

    public function getUser(Request $request){
        $token = explode(' ', $request->header('Authorization'));
        $id = $this->validateJWT($token[1]);

        if(!$id) return json_encode(['status'=>'invalid_token']);
        
        $user = User::find($id);

        if(!$user) return json_encode(['status'=>'invalid_user']);


        return response()->json([
            'nama' => $user->nama,
            'email' => $user->email,
        ]);
    }

    public function createSocialMedia(Request $request){
        $token = explode(' ', $request->header('Authorization'));
        $id = $this->validateJWT($token[1]);
        if(!$id) return json_encode(['status'=>'invalid_token']);

        $this->validate($request, [
            'social_media' => 'string|required',
            'username' => 'string|required'
        ]);

        $socmed = SocialMedia::create([
            'social_media' => $request->social_media,
            'username' => $request->username,
            'user_id' => $id
        ]);

        return response()->json($socmed);
    }
    
    public function updateSocialMedia(Request $request, $id){
        $token = explode(' ', $request->header('Authorization'));
        $user_id = $this->validateJWT($token[1]);
        if(!$user_id) return json_encode(['status'=>'invalid_token']);

        $this->validate($request, [
            'social_media' => 'string',
            'username' => 'string',
        ]);

        $socmed = SocialMedia::find($id);
        if(!$socmed) return json_encode(['status'=>'social media not found']);
        if($socmed->user_id != $user_id) return json_encode(['status'=>'user not authorized to access this social media data']);

        if($request->social_media) $socmed->social_media = $request->social_media;
        if($request->username) $socmed->username = $request->username;
        $socmed->save();
        
        return response()->json($socmed);
    }

    public function deleteSocialMedia(Request $request, $id){
        $token = explode(' ', $request->header('Authorization'));
        $user_id = $this->validateJWT($token[1]);
        if(!$user_id) return json_encode(['status'=>'invalid_token']);


        $socmed = SocialMedia::find($id);
        if(!$socmed) return json_encode(['status'=>'social media not found']);
        if($socmed->user_id != $user_id) return json_encode(['status'=>'user not authorized to access this social media data']);

        $socmed->delete();

        return json_encode(['status'=>'ok']);
    }

    public function readSocialMedia(Request $request){
        $token = explode(' ', $request->header('Authorization'));
        $user_id = $this->validateJWT($token[1]);
        if(!$user_id) return json_encode(['status'=>'invalid_token']);

        $socmed = SocialMedia::WHERE('user_id', 'like', $user_id)->get();
        if(!$socmed) return json_encode(['status'=>'social media not found']);
        
        return response()->json($socmed);
    }

    protected function validateJWT($token){
        $token = explode(".", $token);
        if(sizeof($token) !== 3) return false;
        $signature = hash_hmac('sha256', $token[0] . "." . $token[1], env('SECRET'));
        $decodedSignature = base64_decode($token[2]);
        if($signature !== $decodedSignature)return false;

        $decodedPayload = base64_decode($token[1]);
        $payload = json_decode($decodedPayload);
        $id = $payload->user_id;
        $time = $payload->iat;
        
        if(time() - $time > 86400) return false;

        return $id;
    }
}
