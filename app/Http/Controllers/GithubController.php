<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class GithubController extends Controller
{
    
    public function redirectToGitHub()
    {
        return Socialite::driver('github')->redirect();
    }

    public function handleGitHubCallback()
    {
        try {
            $githubUser = Socialite::driver('github')->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))->stateless()->user();

            $user = User::firstOrCreate(
                ['email' => $githubUser->getEmail()],
                [
                    'name' => $githubUser->getName() ?? $githubUser->getNickname(),
                    'github_id' => $githubUser->getId(),
                    'password' => bcrypt('default123'), // Optional or set null
                ]
            );

            Auth::login($user);

            return redirect('/dashboard');
        } catch (\Exception $e) {
            // dd($e);
            return redirect('/')->with('error', 'GitHub login failed. Please try again.');
        }
    }
}
