<?php

namespace App\Traits;

use App\Models\Setting\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait Categories
{
    public function getCategoryTypes(bool $translate = true): array
    {
        $types = [];
        $configs = config('type.category');

        foreach ($configs as $type => $attr) {
            $plural_type = Str::plural($type);

            $name = $attr['translation']['prefix'] . '.' . $plural_type;

            if (!empty($attr['alias'])) {
                $name = $attr['alias'] . '::' . $name;
            }

            $types[$type] = $translate ? trans_choice($name, 1) : $name;
        }

        return $types;
    }

    public function getCategoryWithoutChildren(int $id): mixed
    {
        return Category::getWithoutChildren()->find($id);
    }

    public function getTransferCategoryId(): mixed
    {
        // 1 hour set cache for same query
        return Cache::remember('transferCategoryId', 60, function () {
            return Category::other()->pluck('id')->first();
        });
    }

    public function isTransferCategory(): bool
    {
        $id = $this->id ?? $this->category->id ?? $this->model->id ?? 0;

        return $id == $this->getTransferCategoryId();
    }

    /**
     * Categories this app creates on demand, keyed by their created_from suffix.
     */
    public function getAutoCategories(): array
    {
        return [
            'loan'               => ['expense', 'Piutang',         '#d4a017'],
            'loan-payment'       => ['income',  'Bayar Piutang',   '#6da252'],
            'investment'         => ['income',  'Investasi',       '#3b82f6'],
            'investment-payment' => ['expense', 'Bayar Investasi', '#a855f7'],
        ];
    }

    public function getAutoCategoryId(string $key): mixed
    {
        [$type, $name, $color] = $this->getAutoCategories()[$key];

        // Keyed by company: the same created_from exists once per company
        return Cache::remember('autoCategoryId.' . company_id() . '.' . $key, 60, function () use ($key, $type, $name, $color) {
            return Category::firstOrCreate(
                ['created_from' => 'core::' . $key],
                ['company_id' => company_id(), 'name' => $name, 'type' => $type, 'color' => $color, 'enabled' => 1]
            )->id;
        });
    }

    public function isAutoCategory(string ...$keys): bool
    {
        $id = $this->id ?? $this->category->id ?? $this->model->id ?? 0;

        foreach ($keys as $key) {
            if ($id == $this->getAutoCategoryId($key)) {
                return true;
            }
        }

        return false;
    }

    public function getChildrenCategoryIds($category)
    {
        $ids = [];

        foreach ($category->sub_categories as $sub_category) {
            $ids[] = $sub_category->id;

            if ($sub_category->sub_categories) {
                $ids = array_merge($ids, $this->getChildrenCategoryIds($sub_category));
            }
        }

        return $ids;
    }
}
