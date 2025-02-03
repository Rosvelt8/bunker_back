<?php

namespace App\Http\Controllers\Newsletter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Newsletter; // Assurez-vous d'avoir ce modèle
use Illuminate\Support\Facades\Mail; // Pour l'envoi des emails
use App\Jobs\SendNewsletterEmail; // Assurez-vous d'avoir ce job

class NewsletterController extends Controller
{
    public function addContact(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletter,email',
        ]);

        $contact = new Newsletter();
        $contact->email = $request->email;
        $contact->save();

        return response()->json(['message' => 'Contact added successfully'], 200);
    }

    public function sendEmails(Request $request)
    {
        $contacts = Newsletter::all();
        $subject = $request->subject;
        $message = $request->message;

        foreach ($contacts as $contact) {
            SendNewsletterEmail::dispatch($contact->email, $subject, $message);
        }

        return response()->json(['message' => 'Emails queued successfully'], 200);
    }
}
