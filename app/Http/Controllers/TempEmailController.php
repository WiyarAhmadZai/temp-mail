<?php

namespace App\Http\Controllers;

use App\Models\Email;
use Illuminate\Http\Request;

class TempEmailController extends Controller
{
    public function index(Request $request)
    {
        $emailId = $request->session()->get('temp_email_id');
        $email = $emailId ? Email::find($emailId) : null;

        // Generate new email if none in session or existing one expired
        if (!$email || $email->isExpired()) {
            $email = Email::generateUniqueEmail();
            $request->session()->put('temp_email_id', $email->id);
        }

        return view('welcome', ['email' => $email]);
    }

    public function generate(Request $request)
    {
        $email = Email::generateUniqueEmail();
        $request->session()->put('temp_email_id', $email->id);

        return redirect()->route('home');
    }
}
