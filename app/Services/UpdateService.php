<?php

namespace App\Services;

use DB;
use App\Models\Chat;
use App\Models\ChatLog;
use App\Models\ChatNote;
use App\Models\ChatTicketLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class UpdateService
{
    public function migrate($version){
        if($version == '1.3'){
            $chats = Chat::get();
            $ticket_logs = ChatTicketLog::get();
            $notes = ChatNote::get();

            foreach($chats as $chat){
                $log = ChatLog::where('contact_id', $chat->contact_id)
                    ->where('entity_type', 'chat')
                    ->where('entity_id', $chat->id)
                    ->first();

                if(!$log){
                    ChatLog::insert([
                        'contact_id' => $chat->contact_id,
                        'entity_type' => 'chat',
                        'entity_id' => $chat->id,
                        'deleted_at' => $chat->deleted_at,
                        'deleted_by' => $chat->deleted_by,
                        'created_at' => $chat->created_at
                    ]);
                }
            }

            foreach($ticket_logs as $ticket_log){
                $log = ChatLog::where('contact_id', $ticket_log->contact_id)
                    ->where('entity_type', 'ticket')
                    ->where('entity_id', $ticket_log->id)
                    ->first();

                if(!$log){
                    ChatLog::insert([
                        'contact_id' => $ticket_log->contact_id,
                        'entity_type' => 'ticket',
                        'entity_id' => $ticket_log->id,
                        'created_at' => $ticket_log->created_at
                    ]);
                }
            }

            foreach($notes as $note){
                $log = ChatLog::where('contact_id', $note->contact_id)
                    ->where('entity_type', 'notes')
                    ->where('entity_id', $note->id)
                    ->first();

                if(!$log){
                    ChatLog::insert([
                        'contact_id' => $note->contact_id,
                        'entity_type' => 'notes',
                        'entity_id' => $note->id,
                        'created_at' => $note->created_at
                    ]);
                }
            }
        } else if($version == '1.4'){
            $seedOutput = Artisan::call('db:seed', [
                '--class' => 'AddonsTableSeeder',
                '--force' => true,
            ]);
        }

        return true;
    }
}