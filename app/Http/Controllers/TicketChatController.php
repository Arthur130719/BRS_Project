<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TicketChatController extends Controller
{
    public function apiIndex($ticket_id)
    {
        $supportTicket = \App\Models\SupportTicket::findOrFail($ticket_id);
        
        // Pastikan tiket milik pelanggan ini
        if ($supportTicket->pelanggan_id != auth()->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $chats = $supportTicket->chats()->with(['pelangganSender', 'userSender'])->get()->map(function($chat) {
            $senderName = 'Unknown';
            if ($chat->sender_type === 'pelanggan') {
                $senderName = $chat->pelangganSender ? $chat->pelangganSender->nama : 'Pelanggan';
            } else {
                $senderName = $chat->userSender ? $chat->userSender->name : 'Admin';
            }

            return [
                'id' => $chat->id,
                'message' => $chat->message,
                'sender_type' => $chat->sender_type,
                'sender_name' => $senderName,
                'created_at' => $chat->created_at->format('Y-m-d H:i:s'),
                'is_me' => $chat->sender_type === 'pelanggan'
            ];
        });

        return response()->json($chats);
    }

    public function apiStore(Request $request, $ticket_id)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $supportTicket = \App\Models\SupportTicket::findOrFail($ticket_id);
        
        if ($supportTicket->pelanggan_id != auth()->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $chat = \App\Models\TicketChat::create([
            'support_ticket_id' => $supportTicket->id,
            'sender_type' => 'pelanggan',
            'sender_id' => auth()->user()->id,
            'message' => $request->message,
        ]);

        // Cari Job Order terkait
        $jobOrder = \App\Models\Ticket::where('support_ticket_id', $supportTicket->id)->first();
        if ($jobOrder) {
            $notifUrl = route('tickets.show', $jobOrder->id, false) . '#chat-section';
            $senderName = auth()->user()->nama ?? auth()->user()->name ?? 'Pelanggan';
            $notifTitle = '💬 [' . $jobOrder->nomor_tiket . '] ' . $senderName;
            
            foreach (['kasir', 'admin'] as $role) {
                $existingNotif = \App\Models\Notifikasi::where('type', 'chat')
                    ->where('target_role', $role)
                    ->where('url', $notifUrl)
                    ->first();

                if ($existingNotif) {
                    $existingNotif->update([
                        'title'      => $notifTitle,
                        'deskripsi'  => \Illuminate\Support\Str::limit($request->message, 50),
                        'is_read'    => false,
                        'updated_at' => now(),
                    ]);
                } else {
                    \App\Models\Notifikasi::create([
                        'type'        => 'chat',
                        'title'       => $notifTitle,
                        'deskripsi'   => \Illuminate\Support\Str::limit($request->message, 50),
                        'target_role' => $role,
                        'url'         => $notifUrl
                    ]);
                }
            }
        }

        return response()->json(['success' => true, 'chat' => $chat]);
    }

    public function webIndexHtml($ticket_id)
    {
        $ticket = \App\Models\Ticket::findOrFail($ticket_id);
        
        if (!$ticket->support_ticket_id) {
            return response('Fitur diskusi hanya tersedia untuk tiket yang diajukan langsung oleh pelanggan.', 200);
        }

        $html = '';
        if ($ticket->supportTicket && $ticket->supportTicket->chats && $ticket->supportTicket->chats->count() > 0) {
            foreach ($ticket->supportTicket->chats as $chat) {
                $isMe = $chat->sender_id == auth()->id() && $chat->sender_type != 'pelanggan';
                $align = $isMe ? 'right' : 'left';
                $bg = $isMe ? 'rgba(59, 130, 246, 0.2)' : 'rgba(255, 255, 255, 0.05)';
                $border = $isMe ? '1px solid rgba(59, 130, 246, 0.4)' : '1px solid rgba(255, 255, 255, 0.1)';
                
                $senderName = 'Unknown';
                if ($chat->sender_type == 'pelanggan' && $chat->pelangganSender) {
                    $senderName = $chat->pelangganSender->nama . ' (Pelanggan)';
                } elseif ($chat->userSender) {
                    $senderName = $chat->userSender->name . ' (' . ucfirst($chat->sender_type) . ')';
                }

                $time = $chat->created_at->format('d M Y, H:i');

                $html .= '<div style="text-align: ' . $align . '; margin-bottom: 15px;">';
                $html .= '<div style="display: inline-block; max-width: 70%; text-align: left; background: ' . $bg . '; border: ' . $border . '; padding: 10px 15px; border-radius: 8px;">';
                $html .= '<div style="font-size: 11px; color: var(--text-3); margin-bottom: 5px;"><strong>' . htmlspecialchars($senderName) . '</strong> &bull; ' . $time . '</div>';
                $html .= '<div style="font-size: 14px; color: var(--text-1); white-space: pre-wrap;">' . htmlspecialchars($chat->message) . '</div>';
                $html .= '</div></div>';
            }
        } else {
            $html = '<div style="text-align: center; color: var(--text-3); padding: 20px;">Belum ada diskusi.</div>';
        }

        return response($html, 200);
    }

    public function store(Request $request, $ticket_id)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $ticket = \App\Models\Ticket::findOrFail($ticket_id);
        
        if (!$ticket->support_ticket_id) {
            return back()->with('error', 'Job Order ini tidak ditautkan ke tiket aduan pelanggan.');
        }

        \App\Models\TicketChat::create([
            'support_ticket_id' => $ticket->support_ticket_id,
            'sender_type' => auth()->user()->role === 'teknisi' ? 'teknisi' : 'admin',
            'sender_id' => auth()->user()->id,
            'message' => $request->message,
        ]);

        // Hapus query string atau fragment sebelumnya jika ada, lalu tambahkan #chat-section
        $url = url()->previous();
        $url = explode('#', $url)[0];
        
        return redirect()->to($url . '#chat-section')->with('success', 'Pesan terkirim.');
    }
}
