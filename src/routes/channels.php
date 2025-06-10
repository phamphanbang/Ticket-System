<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('tickets.{id}', function($id) {
    return true;
});

Broadcast::channel('tickets.{id}.comments', function($id) {
    return true;
});

Broadcast::channel('tickets.{id}.logs', function($id) {
    return true;
});