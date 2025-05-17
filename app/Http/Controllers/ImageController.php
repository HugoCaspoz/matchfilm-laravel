<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class ImageController extends Controller
{
    public function defaultLogo()
    {
        // Crear una imagen SVG simple como logo por defecto
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
            <rect width="100" height="100" fill="#e50914"/>
            <text x="50" y="50" font-family="Arial" font-size="24" text-anchor="middle" fill="white" dominant-baseline="middle">MF</text>
        </svg>';

        return Response::make($svg, 200, [
            'Content-Type' => 'image/svg+xml',
        ]);
    }
}
