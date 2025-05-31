<?php 

namespace App\Services;

use App\Models\Client;

class ClientService {
  public function createClient(array $data): Client
  {
    return Client::firstOrCreate(
      ['email' => $data['email']],
      $data
    );
  }
}