<?php

namespace App\Http\Controllers;

use App\Models\User;

use App\Http\Controllers\Controller;

use Laravel\Socialite\Facades\Socialite;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth ;
use Illuminate\Support\Str ;
use Throwable;


class GoogleAuthController extends Controller
{
    //

    public function redirect()
    {
        return Socialite::driver('google')->redirect(); //Socialite::driver('google'):This tells Socialite to use the Google OAuth driver.
                                                        //->redirect():This sends the user to the Google login/consent page.
    }

    public function callback()
    {
        // dd(Socialite::driver('google')->redirect()->getTargetUrl());
      
        try {
            // Get the user information from Google
            $user = Socialite::driver('google')->stateless()
            ->setHttpClient(new \GuzzleHttp\Client(['verify' => false])) // Ignore SSL certificate
            ->user();//Try to get the user's info from Google (name, email, etc.).
        }   
        
        catch (Throwable $e) {
            return redirect('/')->with('error', 'Google authentication failed.');//If something goes wrong (like the user cancels login), redirect them back to the home page with an error message.
            // dd($e); 

          
        }
        // dd(Socialite::driver('google')->redirect()->getTargetUrl());

        // Check if the user already exists in the database
        $existingUser = User::where('email', $user->email)->first();

        if ($existingUser) {
            // Log the user in if they already exist
            Auth::login($existingUser);
        } else {
            // Otherwise, create a new user and log them in
            $newUser = User::updateOrCreate([
                'email' => $user->email
            ], [
                'name' => $user->name,
                'password' => bcrypt(Str::random(16)), // Set a random password
                'email_verified_at' => now()
            ]);
            Auth::login($newUser);

            // dd('kajbsdkas');
        }

        // dd(Socialite::driwver('google')->redirect()->getTargetUrl());

        // Redirect the user to the dashboard or any other secure page
        return redirect('/dashboard');
        
        
    }
    
}
