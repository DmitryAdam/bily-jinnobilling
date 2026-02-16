<?php

return [

    'quotation_number'      => 'Quotation Number',
    'quotation_date'        => 'Quotation Date',
    'expiry_date'           => 'Expiry Date',
    'total_quotations'      => 'Total Quotations',
    'quantity'              => 'Qty',
    'price'                 => 'Price',
    'send_mail'             => 'Send Email',
    'version'               => 'Version',
    'version_history'       => 'Version History',
    'current_version'       => 'Current',
    'add_payment'           => 'Add Payment',
    'auto_generate'         => 'Auto Generate',

    'statuses' => [
        'draft'             => 'Draft',
        'sent'              => 'Sent',
        'viewed'            => 'Viewed',
        'accepted'          => 'Accepted',
        'rejected'          => 'Rejected',
        'expired'           => 'Expired',
        'cancelled'         => 'Cancelled',
    ],

    'messages' => [
        'email_sent'            => ':type email has been sent!',
        'marked_sent'           => ':type marked as sent!',
        'marked_accepted'       => ':type marked as accepted!',
        'marked_rejected'       => ':type marked as rejected!',
        'revised'               => 'New quotation version created!',
        'converted_to_invoice'  => 'Quotation successfully converted to invoice!',
    ],

    'revision' => [
        'title'             => 'Revise Quotation',
        'create_new'        => 'Create New Revision',
        'description'       => 'You are about to create a new revision of :number. This will create version v:version as a draft copy that you can edit.',
        'notes'             => 'Revision Notes',
        'notes_placeholder' => 'e.g. Adjusted pricing per client feedback, updated scope of work...',
        'notes_hint'        => 'Internal notes for tracking changes between versions. Not visible to customers.',
        'create_version'    => 'Create v:version',
    ],

    'form_description' => [
        'billing'           => 'Billing details are displayed in your quotation. Quotation date is used in the dashboard and reports. Select the date you expect as the expiry date.',
    ],

];
