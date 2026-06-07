<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        ContactMessage::query()->create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'subject' => $request->string('subject')->toString(),
            'message' => $request->string('message')->toString(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->route('pages.contact')
            ->with('contact_success', true);
    }
}
