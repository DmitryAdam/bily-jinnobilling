<?php

namespace App\Utilities;

use App\Models\Document\Document;
use App\Models\Document\DocumentItem;
use App\Models\Document\DocumentItemTax;
use App\Models\Document\DocumentTotal;
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
            $rootId = $quotation->parent_id ?: $quotation->id;
            $newVersion = Document::versionsOf($quotation)->max('version') + 1;

            // Create the new quotation version
            $newQuotation = $quotation->replicate([
                'id',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);

            $newQuotation->parent_id = $rootId;
            $newQuotation->version = $newVersion;
            $newQuotation->revision_notes = $revisionNotes ?: null;
            $newQuotation->status = 'draft';
            $newQuotation->created_from = 'core::quotation-revision';
            $newQuotation->save();

            // Copy items
            foreach ($quotation->items as $item) {
                $newItem = $item->replicate(['id', 'created_at', 'updated_at']);
                $newItem->document_id = $newQuotation->id;
                $newItem->type = Document::QUOTATION_TYPE;
                $newItem->save();

                // Copy item taxes
                foreach ($item->taxes as $tax) {
                    $newTax = $tax->replicate(['id', 'created_at', 'updated_at']);
                    $newTax->document_id = $newQuotation->id;
                    $newTax->document_item_id = $newItem->id;
                    $newTax->type = Document::QUOTATION_TYPE;
                    $newTax->save();
                }
            }

            // Copy totals
            foreach ($quotation->totals as $total) {
                $newTotal = $total->replicate(['id', 'created_at', 'updated_at']);
                $newTotal->document_id = $newQuotation->id;
                $newTotal->type = Document::QUOTATION_TYPE;
                $newTotal->save();
            }

            return $newQuotation;
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
