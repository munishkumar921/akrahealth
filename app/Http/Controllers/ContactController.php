<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestDemo;
use App\Services\DemoRequestService;

class ContactController extends Controller
{
    public function store(RequestDemo $request, DemoRequestService $obj)
    {
        try {
            $obj->store($request);

            return response()->json(['success' => 'Your message has been sent successfully!']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while sending your message. Please try again later.'], 500);
        }
    }
}
