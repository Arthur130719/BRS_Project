<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index()
    {
        // Only admin and kasir can access, usually handled by middleware in web.php
        $tickets = SupportTicket::with('pelanggan')->orderBy('created_at', 'desc')->paginate(15);
        return view('support_tickets.index', compact('tickets'));
    }

    public function updateStatus(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved'
        ]);

        $ticket->update([
            'status' => $request->status
        ]);

        return redirect()->route('support-tickets.index')->with('success', 'Status aduan berhasil diperbarui.');
    }
}
