<?php

namespace App\Http\Controllers;

use App\Actions\Contact\SendContactMessage;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\Page;
use App\Support\ContactRedirect;
use App\Support\Seo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('pages.contact', ['seo' => Seo::forPage(Page::forKey('contact'))]);
    }

    public function store(StoreContactMessageRequest $request, SendContactMessage $sendContactMessage): RedirectResponse
    {
        $sendContactMessage->handle($request->messageData());

        return redirect()->to(ContactRedirect::back())->with('status', __('contact.sent'));
    }
}
