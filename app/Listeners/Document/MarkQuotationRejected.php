<?php

namespace App\Listeners\Document;

use App\Events\Document\QuotationRejected as Event;
use App\Jobs\Document\CreateDocumentHistory;
use App\Traits\Jobs;

class MarkQuotationRejected
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
        $event->document->status = 'rejected';
        $event->document->save();

        $this->dispatch(
            new CreateDocumentHistory(
                $event->document,
                0,
                trans('quotations.messages.marked_rejected', ['type' => trans_choice('general.quotations', 1)])
            )
        );
    }
}
