<?php 

namespace App\Services;

use App\Models\Client;

class ClientService {
  public function createClient($data)
  {
    $client = Client::where('email', $data['client_email'])->first();
    if (!$client) {
      $client = Client::create([
        'name' => $data['client_name'],
        'email' => $data['client_email']
      ]);
    }
    return $client;
  }
}