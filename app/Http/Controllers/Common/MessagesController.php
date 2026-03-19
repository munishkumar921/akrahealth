<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class MessagesController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('chats.index');
    }
}
