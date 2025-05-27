<?php 

namespace App\Services;

use App\Models\Client;

class ClientService {
  public function createClient($data)
  {
    $client = Client::where('email', $data['email'])->first();
    if (!$client) {
      $client = Client::create($data);
    }
    return $client;
  }
}