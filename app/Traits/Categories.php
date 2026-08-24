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

    public function getLoanExpenseCategoryId(): mixed
    {
        return Cache::remember('loanExpenseCategoryId', 60, function () {
            $id = Category::where('created_from', 'core::loan')->pluck('id')->first();

            if (! $id) {
                $category = Category::create([
                    'company_id' => company_id(),
                    'name' => 'Piutang',
                    'type' => 'expense',
                    'color' => '#d4a017',
                    'enabled' => 1,
                    'created_from' => 'core::loan',
                ]);

                $id = $category->id;
            }

            return $id;
        });
    }

    public function getLoanIncomeCategoryId(): mixed
    {
        return Cache::remember('loanIncomeCategoryId', 60, function () {
            $id = Category::where('created_from', 'core::loan-payment')->pluck('id')->first();

            if (! $id) {
                $category = Category::create([
                    'company_id' => company_id(),
                    'name' => 'Bayar Piutang',
                    'type' => 'income',
                    'color' => '#6da252',
                    'enabled' => 1,
                    'created_from' => 'core::loan-payment',
                ]);

                $id = $category->id;
            }

            return $id;
        });
    }

    public function getInvestmentIncomeCategoryId(): mixed
    {
        return Cache::remember('investmentIncomeCategoryId', 60, function () {
            $id = Category::where('created_from', 'core::investment')->pluck('id')->first();

            if (! $id) {
                $category = Category::create([
                    'company_id' => company_id(),
                    'name' => 'Investasi',
                    'type' => 'income',
                    'color' => '#3b82f6',
                    'enabled' => 1,
                    'created_from' => 'core::investment',
                ]);

                $id = $category->id;
            }

            return $id;
        });
    }

    public function getInvestmentExpenseCategoryId(): mixed
    {
        return Cache::remember('investmentExpenseCategoryId', 60, function () {
            $id = Category::where('created_from', 'core::investment-payment')->pluck('id')->first();

            if (! $id) {
                $category = Category::create([
                    'company_id' => company_id(),
                    'name' => 'Bayar Investasi',
                    'type' => 'expense',
                    'color' => '#a855f7',
                    'enabled' => 1,
                    'created_from' => 'core::investment-payment',
                ]);

                $id = $category->id;
            }

            return $id;
        });
    }

    public function isInvestmentCategory(): bool
    {
        $id = $this->id ?? $this->category->id ?? $this->model->id ?? 0;

        return $id == $this->getInvestmentIncomeCategoryId() || $id == $this->getInvestmentExpenseCategoryId();
    }

    public function isLoanCategory(): bool
    {
        $id = $this->id ?? $this->category->id ?? $this->model->id ?? 0;

        return $id == $this->getLoanExpenseCategoryId() || $id == $this->getLoanIncomeCategoryId();
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
