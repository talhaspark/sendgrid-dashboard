<?php

namespace App\Http\Controllers;
use App\Models\SentEmail;
use Illuminate\Http\Request;

class SentEmailController extends Controller
{
   public function index(Request $request)
    {
        $query = SentEmail::latest('sent_at');
 
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('subject',  'like', "%{$s}%")
                  ->orWhere('to_email', 'like', "%{$s}%");
            });
        }
 
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
 
        $emails = $query->paginate(20)->withQueryString();
 
        return view('sent-emails.index', compact('emails'));
    }
 
    public function show(SentEmail $sentEmail)
    {
        return view('sent-emails.show', compact('sentEmail'));
    }
}
