<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Jsonable;

use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\DB;
use App\Notifications\RegisterActivate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

use App\User;
use App\StoreUserRequest;
use DateTime;

define('YOUR_SERVER_URL', 'http://projdad.local');
// Check "oauth_clients" table for next 2 values:
define('CLIENT_ID', '2');
define('CLIENT_SECRET','oVkZcpY4YJXVsJ3bFfCXO6tmBJZahQ4XMfnVMFbF');

class UserControllerAPI extends Controller
{
    public function users(Request $request){
        $users = UserResource::collection(User::paginate(15));
        return response()->json([
            'data' => $users,
            'total' => $users->total(),
            'per_page' => $users->perPage(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'from' => $users->firstItem(),
            'to' => $users->lastItem(),
            'next_page_url' => $users->nextPageUrl(),
            'prev_page_url' => $users->previousPageUrl(),
        ]);
    }

    public function myProfile(Request $request)
    {
        return new UserResource($request->user());
    }

    public function login(Request $request){
 		$http = new \GuzzleHttp\Client;

 		if(filter_var($request->input('userOrEmail'), FILTER_VALIDATE_EMAIL)){
 			$user = User::where('email', $request->input('userOrEmail'))->first();
 			if($user != null){
                if($user->email_verified_at == '') abort(403, 'Account not verified');
                if($user->blocked == 1) abort(403, 'Account Blocked');
            }else{
                abort(403, 'Unknown user');
            }
		}else{
            $user = User::where('username', $request->input('userOrEmail'))->first();
            if($user != null){
                if($user->email_verified_at == '') abort(403, 'Account not verified');
                if($user->blocked == 1) abort(403, 'Account Blocked');
            }else{
                abort(403, 'Unknown user');
            }
        }

 		$response = $http->post(YOUR_SERVER_URL.'/oauth/token', ['form_params' => ['grant_type' => 'password','client_id' => CLIENT_ID,'client_secret' => CLIENT_SECRET,'username' => $request->input('userOrEmail'),'password' => $request->input('password'),'scope' => ''],'exceptions' => false,]);

 		$errorCode= $response->getStatusCode();
 		if ($errorCode=='200') {
 			return json_decode((string) $response->getBody(), true);
 		} else {
 			return response()->json(['message'=>'User credentials are invalid'], $errorCode);
 		}
 	}

 	public function register(Request $request){

        if (!Auth::user()->isManager()){
            return response()->json('You dont have permissions', 500);
        }
 		$validator = Validator::make($request->all(), [
 			'name' => 'required|string',
            'username' => 'required|string|unique:users',
            'email' => 'required|string|email|unique:users',
            'type' => 'required|in:manager,cook,cashier,waiter',
            'photo' => 'nullable|file|mimes:jpeg,bmp,png,jpg',
 		]);

        if($validator->fails()){
        	return response()->json([
        		'message' => 'Error while registering user'
        	], 400);
        }

        $user = new User([
            'name' => $request->input('name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'type' => $request->input('type'),
            'photo_url' => null,
            'activation_token' => str_random(60),
            'password' => '',
        ]);

        if($request->has('photo') && $request->input('photo') != null){
        	$file = $request->file('photo');
            $file_url = str_random(16).'.'.$file->getClientOriginalExtension();
            $user->photo_url = $file_url;
        	Storage::disk('public')->putFileAs('profiles', $file, $file_url);
        }

        $user->save();
        $user->notify(new RegisterActivate($user));
        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
 	}

 	public function logout()
	{
 		\Auth::guard('api')->user()->token()->revoke();
 		\Auth::guard('api')->user()->token()->delete();
 		return response()->json(['msg'=>'Token revoked'], 200);
	}

	public function registerActivate(Request $request, $token){

		$user = User::where('activation_token', $token)->first();
		if ($user && $request->has('password')) {
	        $user->password = bcrypt($request->input('password'));
	        $user->email_verified_at = date("Y-m-d H:i:s");
	        $user->activation_token = '';
	        $user->save();

	        return response()->json([
	        	'message' => 'Account confirmed'
	        ], 201);
    	}
	}

	public function checkRegisted($token){
		$user = User::where('activation_token', $token)->first();
		if (!$user) {
        	return response()->json([
            	'message' => 'This activation token is invalid.'
        	], 403);
        }
        return response()->json([
        	'message' => 'Valid token'
        ], 200);
	}

    public function changePassword(Request $request){
        if($request->has('email') && $request->has('curPassword') && $request->has('newPassword')){
            $user = User::where('email', $request->input('email'))->first();

            if($user != null){
                if (Auth::id()!= $user->id){
                    return response()->json('You dont have permissions', 500);
                }
                if(Hash::check($request->input('curPassword'), $user->password)){
                    $user->password = bcrypt($request->input('newPassword'));
                    $user->save();
                    return response()->json([
                        'message' => 'Password successfully changed'
                    ], 200);
                }else{
                   return response()->json([
                        'message' => 'Current password incorrect'
                    ], 400);
                }
            }else{
                return response()->json([
                    'message' => `User doesn't exist`
                ], 400);
            }
        }else{
            return response()->json([
                'message' => 'Invalid request'
            ], 400);
        }
    }

    public function editProfile(Request $request){
        if($request->has('username')){
            $user = User::where('username', $request->input('username'))->first();

            if (Auth::id() != $user->id && !Auth::user()->isManager()){
                return response()->json('You dont have permissions', 403);
            }

            if($user != null){
                if($request->has('newUsername') && $request->input('newUsername') != null && $request->input('newUsername') != $user->username){
                    $validator = Validator::make(array('username' => $request->input('newUsername')),[
                        'username' => 'string|unique:users',
                    ]);
                    if($validator->fails()){
                        return response()->json([
                            'message' => 'Invalid new username',
                        ], 400);
                    }
                    $user->username = $request->input('newUsername');
                }
                if($request->has('newName') && $request->input('newName') != null && $request->input('newName') != $user->name){
                    $validator = Validator::make(array('name' => $request->input('newName')),[
                        'name' => 'string',
                    ]);
                    if($validator->fails()){
                        return response()->json([
                            'message' => 'Invalid new name',
                        ], 400);
                    }
                    $user->name = $request->input('newName');
                }
                if($request->has('newPhoto')){
                    if($request->file('newPhoto')){
                        $validator = Validator::make(array('photo' => $request->file('newPhoto')),[
                        'photo' => 'file|mimes:jpeg,bmp,png,jpg',
                        ]);
                        if($validator->fails()){
                            return response()->json([
                                'message' => 'Invalid new photo',
                            ], 400);
                        }
                        $oldPhoto_url = $user->photo_url;
                        $newPhoto_url = str_random(16).'.'.$request->file('newPhoto')->getClientOriginalExtension();
                        $user->photo_url = $newPhoto_url;
                        Storage::disk('public')->delete('profiles/'.$oldPhoto_url);
                        Storage::disk('public')->putFileAs('profiles', $request->file('newPhoto'), $newPhoto_url);
                    }elseif($request->input('newPhoto') == 'removePhoto'){
                        Storage::disk('public')->delete('profiles/'.$user->photo_url);
                        $user->photo_url = null;
                    }
                }
                $user->save();
                return new UserResource($user);
            }else{
                return response()->json([
                    'message' => 'Unknown user',
                ], 400);
            }
        }else{
            return response()->json([
                'message' => 'Bad request',
            ], 400);
        }
    }

    public function updateStatus(Request $request, $id){

        if (!Auth::user()->isManager()){
            return response()->json('You dont have permissions', 500);
        }

        $data = $request->validate([
            'blocked' => 'required|numeric|in:0,1',
            ]);
        $user = User::findOrFail($id);
        $user->update($data);

        return response()->json(new UserResource($user), 204);
    }

    public function shift(Request $request){
        $user = User::where('username', $request->user()->username)->first();
        if($user != null && $request->has('shiftStartedTime') && $request->input('shiftStartedTime') != null){
            $date = date("Y-m-d H:i:s", strtotime($request->input('shiftStartedTime')));
            $user->shift_active = 1;
            $user->last_shift_start = $date;
            $user->save();
            return new UserResource($user);

        }elseif($user != null && $request->has('shiftEndedTime') && $request->input('shiftEndedTime') != null){
            $date = date("Y-m-d H:i:s", strtotime($request->input('shiftEndedTime')));
            $user->shift_active = 0;
            $user->last_shift_end = $date;
            $user->save();
            return new UserResource($user);
        }
        return response()->json([
            'message' => "Error on shift operation",
        ], 400);
    }
    public function destroy($id)
    {
        if (!Auth::user()->isManager()){
            return response()->json('You dont have permissions', 500);
        }
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(null, 204);
    }
}
