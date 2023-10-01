<?php

namespace FleetCart\Http\Controllers;

use Illuminate\Routing\Controller;

class LicenseController extends Controller
{


    public function create()
    {
        return view('license.create');
    }

    public function store( $license)
    {
       //
    }
}
