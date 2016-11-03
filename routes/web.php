<?php

Route::get('/', 'Home@index');
Route::get('/map/{trip_id}', 'Map@show');
