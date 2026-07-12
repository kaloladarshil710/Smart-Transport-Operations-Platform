<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\View;

final class LandingController
{
    public function show(): Response
    {
        return Response::html(View::render('system.ready', ['title' => 'TransitOps']));
    }
}
