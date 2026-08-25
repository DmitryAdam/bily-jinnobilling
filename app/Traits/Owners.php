<?php

namespace App\Traits;

trait Owners
{
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function bootOwners()
    {
        static::creating(function ($model) {
            if ($model->isNotOwnable() || ! empty($model->created_by)) {
                return;
            }

            // Console/queue runs have no auth user, fall back to the company owner
            $model->created_by = user_id() ?? company()?->created_by;
        });
    }

    public function isOwnable()
    {
        $ownable = $this->ownable ?: true;

        return ($ownable === true) && in_array('created_by', $this->getFillable());
    }

    public function isNotOwnable()
    {
        return !$this->isOwnable();
    }
}
