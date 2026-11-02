<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Notifications\ContactMessageNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;

class ContactController extends Controller
{
    public function store(ContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // A filled honeypot means a bot: pretend it worked and send nothing.
        if (empty($data['website'])) {
            Notification::route('mail', config('app.support_email'))
                ->notify(new ContactMessageNotification(
                    $data['name'],
                    $data['email'],
                    $data['message'],
                ));
        }

        return back();
    }
}
