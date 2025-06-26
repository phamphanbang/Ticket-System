<?php 

namespace App\Http\Controllers;

use App\Http\Resources\MailResource;
use App\Services\MailService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;  
use Illuminate\Support\Facades\Storage;

class MailController extends Controller
{
  use ApiResponse;

  public function __construct(protected MailService $mailService)
  {

  }
  
  public function index(Request $request, string $ticketId)
  {
    $limit = $request->input('limit', 10);
    $cursor = $request->input('cursor');
    $data = $this->mailService->index($ticketId, $limit, $cursor);

    return $this->success([
      'data' => MailResource::collection($data)
    ], 'Mails retrieved successfully');
  }

  public function store(Request $request , string $ticketId)
  {
    $data = $this->mailService->send($ticketId, $request->all());
    return $this->success([
      'data' => MailResource::make($data)
    ], 'Mail sent successfully');
  } 
}
