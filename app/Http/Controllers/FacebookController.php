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
    if (request()->has('error')) {
        return redirect('/')->with('error', 'Facebook login was cancelled.');
    }

    try {
        $facebookUser = Socialite::driver('facebook')
            ->stateless()
            ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
            ->user();

        $user = User::firstOrCreate(
            ['email' => $facebookUser->getEmail()],
            [
                'name' => $facebookUser->getName(),
                'facebook_id' => $facebookUser->getId(),
                'password' => bcrypt('default123'),
            ]
        );

        Auth::login($user);

        return redirect('/dashboard');

    } catch (\Exception $e) {
        return redirect('/')->with('error', 'Failed to login with Facebook. Please try again.');
    }
}
}
