<?php

Route::get('/', 'Home@index');
Route::get('/map/{trip_id}', 'Map@show');
Route::get('/test', 'OnTime@ninetyninestreet');
