<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FellowsController extends Controller
{
    public function coming_soon()
    {
        $data ['header_title'] = 'Coming Soon';
        return view('admin.associates.fellows_members.coming_soon', $data);
    }
}
