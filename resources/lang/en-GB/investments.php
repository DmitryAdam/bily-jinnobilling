<?php

return [

    'number'            => 'Investment Number',
    'contact_name'      => 'Investor Name',
    'details'           => 'Investment Details',
    'summary'           => 'Summary',
    'total_amount'      => 'Total Amount',
    'paid'              => 'Repaid',
    'remaining'         => 'Outstanding',
    'payment'           => 'Investment Repayment',
    'payment_history'   => 'Repayment History',
    'add_payment'       => 'Add Repayment',
    'no_payments'       => 'No repayments recorded yet.',

    'total_lent'        => 'Total Invested',
    'total_repaid'      => 'Total Returned',
    'total_outstanding' => 'Outstanding',

    'transaction_description' => 'Investment :number - :name',
    'payment_description'     => 'Investment return :number - :name',

    'statuses' => [
        'active'    => 'Active',
        'partial'   => 'Partial',
        'paid'      => 'Repaid',
    ],

    'form_description' => [
        'general'   => 'Enter the investment details including who invested in you, the account, and amount.',
        'edit'      => 'You can edit the date, investor name, and description. Amount and account cannot be changed.',
        'other'     => 'Select the payment method and add an optional reference.',
    ],

    'messages' => [
        'delete'        => ':contact (:amount)',
        'has_payments'  => 'Cannot delete investment that has repayment records. Please delete all repayments first.',
    ],

];
