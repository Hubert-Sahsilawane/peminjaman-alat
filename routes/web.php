<?php

use Illuminate\Support\Facades\Route;
use Dedoc\Scramble\Scramble;

Route::domain('docs.example.com')->group(function () {
    Scramble::registerUiRoute('api');
    Scramble::registerJsonSpecificationRoute('api.json');
});
