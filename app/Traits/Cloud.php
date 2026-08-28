<?php

namespace App\Traits;

use App\Traits\Modules;

trait Cloud
{
    use Modules;

    public function getCloudRolesPageUrl($location = 'user')
    {
        if ($this->moduleIsEnabled('roles')) {
            return route('roles.roles.index');
        }

        return route('apps.app.show', [
            'alias'         => 'roles',
            'utm_source'    => $location,
            'utm_medium'    => 'app',
            'utm_campaign'  => 'roles',
        ]);
    }

    public function isCloud()
    {
        return request()->getHost() == config('cloud.host', 'app.akaunting.com');
    }
}
