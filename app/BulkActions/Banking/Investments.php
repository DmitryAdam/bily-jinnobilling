<?php

namespace App\BulkActions\Banking;

class Investments extends Loans
{
    public $text = 'general.investments';

    public $path = [
        'group' => 'banking',
        'type' => 'investments',
    ];

    public $actions = [
        'delete' => [
            'icon' => 'delete',
            'name' => 'general.delete',
            'message' => 'bulk_actions.message.delete',
            'permission' => 'delete-banking-investments',
        ],
    ];
}
