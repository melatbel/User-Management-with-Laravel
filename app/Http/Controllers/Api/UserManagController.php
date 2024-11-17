<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\UserManagement;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;

class UserManagController extends Controller
{

    public function index()
    {
        $usermanagment = UserManagement::get(); // get all the users list

        if($usermanagment->count()>0)
        {
            return UserResource::collection($usermanagment);
        }
        else
        {
         return response()->json(['message'=> 'No record available'], 200);
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'first_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z]+([ \'-][A-Za-z]+)*$/', 
            ],
            'last_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z]+([ \'-][A-Za-z]+)*$/', 
            ],
            'email' => [
                'required',
                'email:rfc,dns', // Ensures the email follows RFC standard and has a valid DNS (MX record)
                'max:255',
                'unique:usermanagment,email', // Ensures the email is unique in the 'usermanagment' table
                function ($attribute, $value, $fail) {
                    // Check if the email starts or ends with a dot, hyphen, or underscore
                    if (preg_match('/^[.-_]|[.-_]$/', $value)) {
                        return $fail('The ' . $attribute . ' field must be a valid email address.');
                    }
                },
            ],
        ]);
    
    
  
        if ($validator->fails()) {
            return response()->json([
                'message' => 'All fields are mandatory',
                'error' => $validator->messages(),
            ], 422);
        }

       
    
        // Check for existing user with the same details
        $existinguser = UserManagement::where('first_name', $request->first_name)
            ->where('last_name', $request->last_name)
            ->where('email', $request->email)
            ->first();
    
         // If an existing user is found, return a conflict response
        if ($existinguser) {
            return response()->json([
                'message' => 'This user already exists.',
            ], 409); // Conflict status code
        }


     // Create the new user 
     $usermanagment = UserManagement::create([
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'email' => $request->email,
        
    ]);

    return response()->json([
        'message' => 'User Created Successfully',
        'data' => new UserResource($usermanagment),
    ], 201);
}

public function show(Request $request, $identifier)
   {
    $usermanagment = UserManagement::where('id', $identifier)
        ->orWhere('first_name', $identifier)
        ->orWhere('last_name', $identifier)
        ->first();
    if (!$usermanagment) {
        return response()->json(['message' => 'Record not found.'], 404);
}
    return new UserResource($usermanagment);
   }


public function update()
{
    //
}


public function destroy($identifier)
{
    // Attempt to find the user 
    $usermanagment = UserManagement::withTrashed()->find($identifier);

    // Case 1: User does not exist at all
    if (!$usermanagment) {
        return response()->json([
            'message' => 'User does not exist.',
        ], 404);
    }

    // Case 2: User has already been deleted
    if ($usermanagment->trashed()) {
        return response()->json([
            'message' => 'User has already been deleted.',
        ], 400); 
    }

    $usermanagment->delete();

    return response()->json([
        'message' => 'User deleted successfully.',
    ], 200); 
}


}
