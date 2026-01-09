<?php

namespace App\Http\Controllers;

class ErrorController extends Controller
{
    public function unauthorized()
    {
        return view('errors.unauthorized');
    }

    public function unauthorizedAccess()
    {
        return view('errors.unauthorized-access');
    }
}
