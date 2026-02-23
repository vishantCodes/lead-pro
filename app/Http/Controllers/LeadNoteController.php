<?php

namespace App\Http\Controllers;

use App\Models\ClientNote;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Http\Request;

class LeadNoteController extends Controller
{
    /**
     * Store a new note for a lead.
     */
    public function store(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        $request->validate([
            'note' => 'required|string|max:5000',
        ]);

        $note = $lead->clientNotes()->create([
            'tenant_id'  => $lead->tenant_id,
            'lead_id'    => $lead->id,
            'created_by' => auth()->id(),
            'note'       => $request->note,
        ]);

        // Log activity
        LeadActivity::log($lead, 'note_added', 'Added a note.');

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Note added successfully.');
    }

    /**
     * Delete a note.
     */
    public function destroy(Lead $lead, ClientNote $note)
    {
        $this->authorize('update', $lead);

        // Ensure note belongs to the lead
        if ($note->lead_id !== $lead->id) {
            abort(403);
        }

        $note->delete();

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Note deleted.');
    }
}
