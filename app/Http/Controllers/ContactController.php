<?php

namespace App\Http\Controllers;

use App\Actions\Contact\SendContactMessage;
use App\Http\Requests\StoreContactMessageRequest;
use App\Support\ContactRedirect;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function store(StoreContactMessageRequest $request, SendContactMessage $sendContactMessage): RedirectResponse
    {
        $sendContactMessage->handle($request->messageData());

        return redirect()->to(ContactRedirect::back())->with('status', __('contact.sent'));
    }
}
