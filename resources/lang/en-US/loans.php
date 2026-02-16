<?php

return [

    'loan_number'       => 'Loan Number',
    'contact_name'      => 'Contact Name',
    'loan_details'      => 'Loan Details',
    'summary'           => 'Summary',
    'total_amount'      => 'Total Amount',
    'paid'              => 'Paid',
    'remaining'         => 'Remaining',
    'payment'           => 'Loan Payment',
    'payment_history'   => 'Payment History',
    'add_payment'       => 'Add Payment',
    'no_payments'       => 'No payments recorded yet.',

    'statuses' => [
        'active'    => 'Active',
        'partial'   => 'Partial',
        'paid'      => 'Paid',
    ],

    'form_description' => [
        'general'   => 'Enter the loan details including who you are lending to, the account, and amount.',
        'edit'      => 'You can edit the date, contact name, and description. Amount and account cannot be changed.',
        'other'     => 'Select the payment method and add an optional reference.',
    ],

    'messages' => [
        'delete'        => ':contact (:amount)',
        'has_payments'  => 'Cannot delete loan that has payment records. Please delete all payments first.',
    ],

];
