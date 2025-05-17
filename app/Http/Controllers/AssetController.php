<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class AssetController extends Controller
{
    public function serveCSS($filename)
    {
        $path = public_path('css/' . $filename);

        if (!File::exists($path)) {
            abort(404);
        }

        $content = File::get($path);
        $type = 'text/css';

        return Response::make($content, 200, [
            'Content-Type' => $type,
            'Cache-Control' => 'public, max-age=86400'
        ]);
    }

    public function serveJS($filename)
    {
        $path = public_path('js/' . $filename);

        if (!File::exists($path)) {
            abort(404);
        }

        $content = File::get($path);
        $type = 'application/javascript';

        return Response::make($content, 200, [
            'Content-Type' => $type,
            'Cache-Control' => 'public, max-age=86400'
        ]);
    }
}
