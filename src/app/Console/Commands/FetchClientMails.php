<?php

namespace App\Console\Commands;

use App\Constants\TicketStatus;
use App\Helpers\MailHelper;
use App\Models\Client as ModelsClient;
use App\Models\TicketEmail;
use App\Models\Ticket;
use App\Services\ClientService;
use App\Services\CommentService;
use App\Services\TicketMailService;
use App\Services\TicketService;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FetchClientMails extends Command
{
    public function __construct(
        protected ClientService $clientService,
        protected MailHelper $mailHelper,
        protected TicketService $ticketService,
        protected CommentService $commentService
    ) {
        parent::__construct();
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticket:fetch-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch emails via IMAP and create tickets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching emails...');
        Log::info('YourCommand is running at ' . now());
        $IMAP_client = Client::account('default');
        $IMAP_client->connect();

        $fetchTime = Carbon::now()->subHour();
        $messages = $IMAP_client->getFolder('INBOX')->messages()->since($fetchTime)->get();

        foreach ($messages as $message) {
            $data = $this->mailHelper->processIMAPEmail($message);
            $this->info('process email : ' . $data['message_id']);
            $isReply = $data['in_reply_to'];
            $hasTicketSubject = str_contains($data['subject'], '[ESReport]');

            if (!$isReply && !$hasTicketSubject) {
                continue;
            }

            $mail = TicketEmail::where('message_id', $data['message_id'])->first();
            if ($mail) {
                continue;
            }

            if (!$data['in_reply_to']) {
                $this->info('create ticket : ' . $data['message_id']);
                $ticket_data = [
                    'client_email' => $data['from_email'],
                    'title' => $data['subject'],
                    'description' => $data['body'],
                ];
                $ticket = $this->ticketService->store($ticket_data);
                $mail = $ticket->ticketEmails()->create([
                    'message_id' => $data['message_id'],
                    'in_reply_to' => $data['in_reply_to'],
                    'references' => is_array($data['references']) ? json_encode($data['references']) : $data['references'],
                    'from_email' => $data['from_email'],
                    'to_email' => $data['to_email'],
                    'subject' => $data['subject'],
                    'body' => $data['body'],
                    'received_at' => Carbon::now(),
                ]);
                $this->handleAttachments($mail, $message);
                $this->info('Ticket' . $ticket->subject . ' created successfully.');
                Log::info('fetch ticket ' . $ticket->subject);
                continue;
            }

            $replyTarget = TicketEmail::whereIn('message_id', $data['references'])
                ->orWhere('message_id', $data['in_reply_to'])
                ->latest()
                ->first();

            if ($replyTarget) {
                $ticket = $replyTarget->ticket;
                $this->info('reply ticket : ' . $ticket->subject);
                $receivedEmail = $ticket->ticketEmails()->create([
                    'message_id' => $data['message_id'],
                    'in_reply_to' => $data['in_reply_to'],
                    'references' => is_array($data['references']) ? json_encode($data['references']) : $data['references'],
                    'from_email' => $data['from_email'],
                    'to_email' => $data['to_email'],
                    'subject' => $data['subject'],
                    'body' => $data['body'],
                    'received_at' => Carbon::now(),
                ]);
                $this->handleAttachments($receivedEmail, $message);
                $this->info("Reply added to ticket ID {$ticket->id}");
                Log::info("Reply processed for ticket ID {$ticket->id}");
            }
            // $message->setFlag('Seen');
        }



        $IMAP_client->disconnect();

        $this->info('Emails fetched successfully.');
    }

    private function handleAttachments(TicketEmail $email, $message): void
    {
        foreach ($message->getAttachments() as $attachment) {
            $filename = uniqid() . '_' . $attachment->getName();
            Log::info('process attachment : ' . $filename);
            // $path = storage_path('app/public/attachments/' . $filename);
            // $attachment->save($path);
            $fileExtension = $attachment->getExtension();
            $contentType = $attachment->getMimeType();
            $fileSize = $attachment->getSize();

            // $filePath = $attachment->save("app/private/tickets/{$email->ticket_id}",$filename);
            $relativePath = "tickets/{$email->ticket_id}";
            $storagePath = storage_path("app/private/{$relativePath}");
            if (!Storage::disk('local')->exists($relativePath)) {
                Storage::disk('local')->makeDirectory($relativePath);
            }
            $attachment->save($storagePath,$filename);
            // $filePath = Storage::disk('local')->putFileAs("tickets/{$email->ticket_id}", $attachment->getContents(), $filename);
            $email->attachments()->create([
                'ticket_id' => $email->ticket_id,
                'file_name' => $filename,
                'file_extension' => $fileExtension,
                'file_path' => $relativePath,
                'file_size' => $fileSize,
                'content_type' => $contentType
            ]);
        }
    }
}
