<?php

namespace Modules\CustomAdmin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MetaWebhookController extends Controller
{
    public function receiveWebhook(Request $request) { return response()->json(['ok'=>true]); }
}