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
     * Get the display number with version suffix.
     */
    public function getDisplayNumber(Document $quotation): string
    {
        return $quotation->document_number . '-v' . $quotation->version;
    }

    /**
     * Create a new version of the quotation.
     */
    public function createNewVersion(Document $quotation, string $revisionNotes = ''): Document
    {
        return DB::transaction(function () use ($quotation, $revisionNotes) {
            // Find the root quotation ID (the one with parent_id = 0)
            $rootId = $quotation->parent_id ?: $quotation->id;

            // Get the highest version number across all versions
            $maxVersion = Document::where(function ($q) use ($rootId) {
                $q->where('id', $rootId)
                  ->orWhere('parent_id', $rootId);
            })->where('type', Document::QUOTATION_TYPE)->max('version');

            $newVersion = $maxVersion + 1;

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
        $rootId = $quotation->parent_id ?: $quotation->id;

        return Document::where(function ($q) use ($rootId) {
            $q->where('id', $rootId)
              ->orWhere('parent_id', $rootId);
        })->where('type', Document::QUOTATION_TYPE)
          ->orderBy('version', 'asc')
          ->get();
    }

    /**
     * Get the latest version of a quotation.
     */
    public function getLatestVersion(Document $quotation): Document
    {
        $rootId = $quotation->parent_id ?: $quotation->id;

        return Document::where(function ($q) use ($rootId) {
            $q->where('id', $rootId)
              ->orWhere('parent_id', $rootId);
        })->where('type', Document::QUOTATION_TYPE)
          ->orderBy('version', 'desc')
          ->first();
    }
}
