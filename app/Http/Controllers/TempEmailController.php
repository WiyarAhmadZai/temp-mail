<?php

namespace App\Http\Controllers;

use App\Models\Email;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

        // Invalidate old cache
        $this->clearInboxCache($email->id);

        Log::channel('tempmail')->info('New email generated', [
            'email' => $email->email,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('home');
    }

    public function inbox(Request $request)
    {
        $email = $this->resolveEmail($request);
        $perPage = config('tempmail.messages_per_page');
        $messages = $email->messages()->latest()->paginate($perPage);

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
     * Cached for 3 seconds to reduce DB load under frequent polling.
     */
    public function poll(Request $request): JsonResponse
    {
        $email = $this->resolveEmail($request);

        $data = Cache::remember(
            "inbox:{$email->id}",
            3, // seconds
            function () use ($email) {
                $messages = $email->messages()->latest()->get()->map(fn ($msg) => [
                    'id' => $msg->id,
                    'sender' => $msg->sender,
                    'subject' => $msg->subject,
                    'preview' => Str::limit(strip_tags($msg->body), 100),
                    'time' => $msg->created_at->diffForHumans(),
                    'url' => route('message.show', $msg->id),
                ]);

                return [
                    'email' => $email->email,
                    'expired' => $email->isExpired(),
                    'expires_at' => $email->expires_at->diffForHumans(),
                    'message_count' => $messages->count(),
                    'messages' => $messages,
                ];
            }
        );

        return response()->json($data);
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

    private function clearInboxCache(int $emailId): void
    {
        Cache::forget("inbox:{$emailId}");
    }
}
