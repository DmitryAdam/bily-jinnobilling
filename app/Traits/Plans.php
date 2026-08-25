<?php

namespace App\Traits;

/**
 * Plan limits are disabled on this self-hosted install: every check passes.
 * To restore them, put back the akaunting.com SiteApi lookup this replaced.
 */
trait Plans
{
    public function clearPlansCache(): void
    {
        //
    }

    public function getUserLimitOfPlan(): object
    {
        return $this->getPlanLimitByType('user');
    }

    public function getCompanyLimitOfPlan(): object
    {
        return $this->getPlanLimitByType('company');
    }

    public function getInvoiceLimitOfPlan(): object
    {
        return $this->getPlanLimitByType('invoice');
    }

    public function getAnyActionLimitOfPlan(): object
    {
        return $this->getPlanLimitByType('any');
    }

    public function getPlanLimitByType($type): object
    {
        return (object) [
            'action_status' => true,
            'view_status'   => true,
            'message'       => 'Success',
        ];
    }
}
