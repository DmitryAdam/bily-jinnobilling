<?php

namespace App\Listeners\Document;

use App\Events\Document\QuotationAccepted as Event;
use App\Jobs\Document\CreateDocumentHistory;
use App\Traits\Jobs;

class MarkQuotationAccepted
{
    use Jobs;

    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function handle(Event $event)
    {
        $event->document->status = 'accepted';
        $event->document->save();

        $this->dispatch(
            new CreateDocumentHistory(
                $event->document,
                0,
                trans('quotations.messages.marked_accepted', ['type' => trans_choice('general.quotations', 1)])
            )
        );
    }
}
