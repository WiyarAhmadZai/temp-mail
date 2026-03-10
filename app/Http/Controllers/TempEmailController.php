<?php

namespace App\Http\Controllers;

use App\Models\Email;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TempEmailController extends Controller
{
    public function index(Request $request)
    {
        $email = $this->resolveEmail($request);

        return view('welcome', [
            'email' => $email,
            'messageCount' => $email->messages()->count(),
        ]);
    }

    public function generate(Request $request)
    {
        $email = Email::generateUniqueEmail();
        $request->session()->put('temp_email_id', $email->id);

        return redirect()->route('home');
    }

    public function inbox(Request $request)
    {
        $email = $this->resolveEmail($request);
        $messages = $email->messages()->latest()->get();

        return view('inbox', [
            'email' => $email,
            'messages' => $messages,
        ]);
    }

    public function showMessage(Request $request, int $id)
    {
        $email = $this->resolveEmail($request);
        $message = $email->messages()->findOrFail($id);

        return view('message', [
            'email' => $email,
            'message' => $message,
        ]);
    }

    /**
     * AJAX endpoint: returns messages + email status as JSON for polling.
     */
    public function poll(Request $request): JsonResponse
    {
        $email = $this->resolveEmail($request);

        $messages = $email->messages()->latest()->get()->map(fn ($msg) => [
            'id' => $msg->id,
            'sender' => $msg->sender,
            'subject' => $msg->subject,
            'preview' => \Illuminate\Support\Str::limit(strip_tags($msg->body), 100),
            'time' => $msg->created_at->diffForHumans(),
            'url' => route('message.show', $msg->id),
        ]);

        return response()->json([
            'email' => $email->email,
            'expired' => $email->isExpired(),
            'expires_at' => $email->expires_at->diffForHumans(),
            'message_count' => $messages->count(),
            'messages' => $messages,
        ]);
    }

    /**
     * Resolve the active temp email from session, or generate a new one.
     */
    private function resolveEmail(Request $request): Email
    {
        $emailId = $request->session()->get('temp_email_id');
        $email = $emailId ? Email::find($emailId) : null;

        if (!$email || $email->isExpired()) {
            $email = Email::generateUniqueEmail();
            $request->session()->put('temp_email_id', $email->id);
        }

        return $email;
    }
}
