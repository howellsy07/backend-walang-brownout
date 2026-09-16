<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function (): void {
    $this->comment('Small tasks become big progress.');
})->purpose('Display a short motivational message');

