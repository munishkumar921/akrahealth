<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatusController extends Controller
{
    /**
     * updateStatus
     *
     * @param  mixed  $request
     * @return void
     */
    public function updateStatus(Request $request)
    {
        $table = $request->post('table');
        $id = $request->post('id');
        $status = $request->post('is_active');

        DB::table($table)->where('id', $id)->update([
            'is_active' => $status,
        ]);
    }
}
