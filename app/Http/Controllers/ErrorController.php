<?php

namespace App\Http\Controllers;

class ErrorController extends Controller
{
    public function unauthorizedAccess()
    {
        return view('errors.unauthorized-access');
    }
}
