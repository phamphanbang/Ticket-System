<?php

namespace App\Services;

use App\Constants\PaginateConstant;
use App\Constants\TicketStatus;
use App\Constants\UserRoles;
use App\Exceptions\InvalidTicketAssignmentException;
use App\Mail\ClientAdminUpdateTicket;
use App\Mail\ClientStaffDelayTicket;
use App\Mail\ClientTicketCreated;
use App\Mail\ClientTicketIsClosed;
use App\Mail\ClientTicketIsConfirmed;
use App\Mail\ClientTicketIsResolved;
use App\Mail\StaffAssignedToNewTicket;
use App\Mail\StaffClientRejectTicket;
use App\Mail\StaffTicketIsClosed;
use App\Mail\StaffUnassignedToTicket;
use App\Models\Client;
use App\Models\Ticket;
use App\Models\User;
use App\Validators\TicketValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Mail;

class TicketService
{
  public function __construct(
    protected ClientService $clientService,
    protected TicketMailService $ticketMailService
  ) {
    // Constructor to inject ClientService dependency

  }
  public function getListTicket($request)
  {
    $user = auth()->user();
    $list = [];
    $query = Ticket::query()->with('assignTo');

    if ($user->role == UserRoles::STAFF->value) {
      $query = $query->where('assign_to', $user->id);
    }

    if ($request->filled('search')) {
      $searchTerm = $request->input('search');
      $query->where(function ($q) use ($searchTerm) {
        $q->where('title', 'like', "%{$searchTerm}%")
          ->orWhere('description', 'like', "%{$searchTerm}%");
      });
    }

    // if ($request->filled('client_email')) {
    //   $clientEmail = $request->input('client_email');
    //   $query->where('client_email', 'like', "%{$clientEmail}%");
    // }

    if ($request->filled('staff_id')) {
      $staffId = $this->normalizeToArray($request->input('staff_id'));
      $query->whereIn('assign_to', $staffId);
    }

    if ($request->filled('priority')) {
      $staffId = $this->normalizeToArray($request->input('priority'));
      $query->whereIn('priority', $staffId);
    }

    if ($request->filled('deadline_from')) {
      $query->whereDate('deadline', '>=', $request->input('deadline_from'));
    }
    if ($request->filled('deadline_to')) {
      $query->whereDate('deadline', '<=', $request->input('deadline_to'));
    }

    if ($request->filled('created_from')) {
      $query->whereDate('created_at', '>=', $request->input('created_from'));
    }
    if ($request->filled('created_to')) {
      $query->whereDate('created_at', '<=', $request->input('created_to'));
    }

    if ($request->filled('status')) {
      $statuses = $this->normalizeToArray($request->input('status'));
      $query->whereIn('status', $statuses);
    }

    if ($request->filled('is_search') && $request->boolean('is_search') == true) {
      $perPage = $request->input('perPage', PaginateConstant::DEFAULT_PER_PAGE->value);
      $list = $query->limit($perPage)->get();
    } else {
      $statusList = TicketStatus::list();
      $filterStatusList = TicketStatus::listValue();
      if ($request->filled('status')) {
        $filterStatusList = $this->normalizeToArray($request->input('status'));
      }

      foreach ($statusList as $status) {
        if (!in_array($status->value, $filterStatusList)) {
          $list[$status->column_label()] = [];
          continue;
        }
        $tempQuery = (clone $query)->where('status', $status->value);
        $list[$status->column_label()] = $tempQuery->get();
      }
    }

    return $list;
  }

  function normalizeToArray(string $input): array
{
    // Try JSON decode first
    $decoded = json_decode($input, true);

    if (is_array($decoded)) {
        return array_map('intval', $decoded);
    }

    // Fallback to comma-separated string
    return array_map('intval', explode(',', $input));
}

  public function createTicket($data)
  {
    $ticket = Ticket::create($data);

    return $ticket->load(['assignTo', 'assignTo.role', 'client']);
  }

  public function adminCreateTicket(array $data)
  {
    $client_data = [
      'name' => $data['client_name'],
      'email' => $data['client_email']
    ];
    $client = $this->clientService->createClient($client_data);
    $data['client_id'] = $client->id;

    $ticket = $this->createTicket($data);
    if ($ticket->assign_to) {
      Mail::to($ticket->assignTo->email)
        ->queue(new StaffAssignedToNewTicket($ticket));
    }

    Mail::to($ticket->client->email)
      ->queue(new ClientTicketCreated($ticket));

    return $ticket;
  }

  public function update($data, $id)
  {
    $ticket = Ticket::where('id', $id)->first();

    TicketValidator::checkTicketExists($ticket);
    TicketValidator::checkTicketStatus($ticket, TicketStatus::New->value);

    $oldTitle = $ticket->title;
    $oldDescription = $ticket->description;
    $oldAssignId = $ticket->assign_to;
    $newAssignId = $data['assign_to'] ?? null;

    $ticket->update($data);

    $oldUser = $oldAssignId ? User::find($oldAssignId)->first() : null;
    $newUser = $newAssignId ? User::find($newAssignId)->first() : null;

    if (is_null($oldAssignId) && $newUser) {
      Mail::to($newUser->email)
        ->queue(new StaffAssignedToNewTicket($ticket));
    }

    if ($oldUser && is_null($newAssignId)) {
      Mail::to($oldUser->email)->queue(new StaffUnassignedToTicket($ticket, $oldUser));
    }
    if ($oldUser && $newUser && $oldAssignId !== $newAssignId) {
      Mail::to($newUser->email)->queue(new StaffAssignedToNewTicket($ticket));
      Mail::to($oldUser->email)->queue(new StaffUnassignedToTicket($ticket, $oldUser));
    }
    if ($oldAssignId !== $newAssignId || $oldTitle !== $ticket->title || $oldDescription !== $ticket->description) {
      Mail::to($ticket->client->email)->queue(new ClientAdminUpdateTicket($ticket));
    }

    return $ticket;
  }

  public function staffConfirmTicket($data, $id)
  {
    $user = auth()->user();
    $ticket = Ticket::where('id', $id)->first();

    TicketValidator::checkTicketExists($ticket);
    TicketValidator::checkTicketStatus($ticket, TicketStatus::New->value);
    TicketValidator::checkStaffIsAssignedToTicket($ticket, $user->id);

    $ticket->update($data);

    Mail::to($ticket->client->email)->queue(new ClientTicketIsConfirmed($ticket));

    return $ticket;
  }

  public function staffResolveTicket($data, $id)
  {
    $user = auth()->user();
    $ticket = Ticket::where('id', $id)->first();

    TicketValidator::checkTicketExists($ticket);
    TicketValidator::checkTicketStatus($ticket, TicketStatus::InProgress->value);
    TicketValidator::checkStaffIsAssignedToTicket($ticket, $user->id);

    $ticket->update($data);

    Mail::to($ticket->client->email)->queue(new ClientTicketIsResolved($ticket));

    return $ticket;
  }

  public function staffDelayTicket($data, $id)
  {
    $user = auth()->user();
    $ticket = Ticket::where('id', $id)->first();

    TicketValidator::checkTicketExists($ticket);
    TicketValidator::checkTicketStatus($ticket, TicketStatus::InProgress->value);
    TicketValidator::checkStaffIsAssignedToTicket($ticket, $user->id);

    $ticket->update($data);

    Mail::to($ticket->client->email)->queue(new ClientStaffDelayTicket($ticket));

    return $ticket;
  }

  public function clientRejectTicket($data, $id)
  {
    $ticket = Ticket::where('id', $id)->first();
    TicketValidator::checkTicketExists($ticket);
    TicketValidator::checkTicketStatus($ticket, TicketStatus::Resolved->value);

    $ticket->update($data);

    Mail::to($ticket->assignTo->email)->queue(new StaffClientRejectTicket($ticket));

    return $ticket;
  }

  public function closeTicket($data, $id)
  {
    $ticket = Ticket::where('id', $id)->first();
    $user = auth()->user();

    TicketValidator::checkTicketExists($ticket);
    if (!$user) {
      TicketValidator::checkTicketStatus($ticket, TicketStatus::Resolved->value);
    }
    if ($user && $user->isStaff()) {
      throw new InvalidTicketAssignmentException(__('error.you_are_not_admin'));
    }
    $ticket->update($data);

    Mail::to($ticket->assignTo->email)->queue(new StaffTicketIsClosed($ticket));

    if ($user) {
      Mail::to($ticket->client->email)->queue(new ClientTicketIsClosed($ticket));
    }

    return $ticket;
  }

  public function getTicketById($id)
  {
    $ticket = Ticket::where('id', $id)->first();
    TicketValidator::checkTicketExists($ticket);

    return $ticket->load(['assignTo', 'client']);
  }

  public function createTicketFromMail($data, Client $ticket_client)
  {
    $ticket = Ticket::create([
      'title' => $data['subject'],
      'description' => $data['body'],
      'client_id' => $ticket_client->id,
      'status' => TicketStatus::New->value,
      'deadline' => now()->addDays(7),
    ]);

    $mail = $this->ticketMailService->createTicketMail([
      ...$data,
      'ticket_id' => $ticket->id,
    ]);

    $ticket->created_mail_id = $mail->id;
    $ticket->save();

    return $ticket->load(['assignTo', 'client']);
  }
}
