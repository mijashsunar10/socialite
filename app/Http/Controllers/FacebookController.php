<?php
namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FacebookController extends Controller
{
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
{
    // Check if the user cancelled the login
    //If user cancel the facebook login then error will occur;
    if (request()->has('error')) {
        return redirect('/')->with('error', 'Facebook login was cancelled.');
    }

    try {
        $facebookUser = Socialite::driver('facebook')
            ->stateless()//avoid using session to retireeve daata
            ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))// Disable SSL verification (not recommended for production)
            ->user();// Get user data from Facebook

            //Create the new user or retreve the existing one based on the email

        $user = User::firstOrCreate(
            ['email' => $facebookUser->getEmail()], //Check if the user with that email exists
            [
                'name' => $facebookUser->getName(),  //Sets user name
                'facebook_id' => $facebookUser->getId(), //Store Facebook Id
                'password' => bcrypt('default123'), //default password (should be change later)
            ]
        );

        //Log the user in
        Auth::login($user);

        //redirect dashboard
        return redirect('/dashboard');

    } catch (\Exception $e) {
        //If any error occur the redirect with error message  thatswhy try catch is used
        return redirect('/')->with('error', 'Failed to login with Facebook. Please try again.');
    }
}
}
