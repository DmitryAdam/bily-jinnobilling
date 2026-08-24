<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->string('investment_number')->nullable();
            $table->integer('account_id');
            $table->integer('transaction_id')->nullable();
            $table->double('amount', 15, 4);
            $table->string('currency_code', 3);
            $table->double('currency_rate', 15, 8)->default(1);
            $table->string('contact_name');
            $table->text('description')->nullable();
            $table->datetime('issued_at');
            $table->string('payment_method');
            $table->string('reference')->nullable();
            $table->string('status')->default('active');
            $table->string('created_from')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'investment_number']);
            $table->index(['company_id', 'status']);
            $table->index('account_id');
            $table->index('transaction_id');
        });

        Schema::create('investment_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->integer('investment_id');
            $table->integer('transaction_id')->nullable();
            $table->integer('account_id');
            $table->double('amount', 15, 4);
            $table->string('currency_code', 3);
            $table->double('currency_rate', 15, 8)->default(1);
            $table->datetime('paid_at');
            $table->string('payment_method');
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->string('created_from')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('investment_id');
            $table->index('transaction_id');
        });

        $this->createPermissions();
    }

    public function down(): void
    {
        $ids = \DB::table('permissions')->where('name', 'like', '%-banking-investments')->pluck('id');

        \DB::table('role_permissions')->whereIn('permission_id', $ids)->delete();
        \DB::table('user_permissions')->whereIn('permission_id', $ids)->delete();
        \DB::table('permissions')->whereIn('id', $ids)->delete();

        Schema::dropIfExists('investment_payments');
        Schema::dropIfExists('investments');
    }

    protected function createPermissions(): void
    {
        $actions = [
            'create' => 'Create',
            'read'   => 'Read',
            'update' => 'Update',
            'delete' => 'Delete',
        ];

        $role_ids = \DB::table('roles')->pluck('id');

        foreach ($actions as $action => $display) {
            $name = $action . '-banking-investments';

            $permission_id = \DB::table('permissions')->where('name', $name)->value('id');

            if (! $permission_id) {
                $permission_id = \DB::table('permissions')->insertGetId([
                    'name' => $name,
                    'display_name' => $display . ' Banking Investments',
                    'description' => $display . ' Banking Investments',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($role_ids as $role_id) {
                $exists = \DB::table('role_permissions')
                    ->where('role_id', $role_id)
                    ->where('permission_id', $permission_id)
                    ->exists();

                if (! $exists) {
                    \DB::table('role_permissions')->insert([
                        'role_id' => $role_id,
                        'permission_id' => $permission_id,
                    ]);
                }
            }
        }
    }
};
