<?php

namespace App\Utilities;

use App\Models\Document\Document;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuotationVersion
{
    /**
     * Create a new version of the quotation.
     */
    public function createNewVersion(Document $quotation, string $revisionNotes = ''): Document
    {
        return DB::transaction(function () use ($quotation, $revisionNotes) {
            // Cloneable already deep-copies items, their taxes and the totals
            $new = $quotation->duplicate();

            $new->fill([
                // onCloning hands out a fresh number; a revision keeps the original
                'document_number' => $quotation->document_number,
                'parent_id'       => $quotation->parent_id ?: $quotation->id,
                'version'         => Document::versionsOf($quotation)->max('version') + 1,
                'revision_notes'  => $revisionNotes ?: null,
                'created_from'    => 'core::quotation-revision',
            ])->save();

            return $new;
        });
    }

    /**
     * Get all versions of a quotation, ordered by version.
     */
    public function getVersionHistory(Document $quotation): Collection
    {
        return Document::versionsOf($quotation)->orderBy('version')->get();
    }

    /**
     * Get the latest version of a quotation.
     */
    public function getLatestVersion(Document $quotation): Document
    {
        return Document::versionsOf($quotation)->orderByDesc('version')->first();
    }
}
