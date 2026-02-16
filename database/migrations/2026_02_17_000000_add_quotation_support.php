<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('documents', 'version')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->unsignedInteger('version')->default(1)->after('parent_id');
            });
        }

        // Update unique index to include version (for quotation versioning)
        $tableName = DB::getTablePrefix() . 'documents';
        $indexes = collect(DB::select("SHOW INDEX FROM `{$tableName}`"))->pluck('Key_name')->unique();

        $oldIndexName = DB::getTablePrefix() . 'documents_document_number_deleted_at_company_id_type_unique';

        if ($indexes->contains($oldIndexName)) {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropUnique(['document_number', 'deleted_at', 'company_id', 'type']);
            });
        }

        if (! $indexes->contains('documents_doc_num_del_comp_type_ver_unique')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->unique(
                    ['document_number', 'deleted_at', 'company_id', 'type', 'version'],
                    'documents_doc_num_del_comp_type_ver_unique'
                );
            });
        }

        // Seed quotation permissions
        $this->createPermissions();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropUnique('documents_doc_num_del_comp_type_ver_unique');
            $table->unique(['document_number', 'deleted_at', 'company_id', 'type']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('version');
        });

        $this->deletePermissions();
    }

    private function createPermissions()
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $permissions = [
            'create-sales-quotations',
            'read-sales-quotations',
            'update-sales-quotations',
            'delete-sales-quotations',
        ];

        foreach ($permissions as $permissionName) {
            if (! DB::table('permissions')->where('name', $permissionName)->exists()) {
                DB::table('permissions')->insert([
                    'name' => $permissionName,
                    'display_name' => ucwords(str_replace('-', ' ', $permissionName)),
                    'description' => ucwords(str_replace('-', ' ', $permissionName)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Attach permissions to admin role (role_id = 1)
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');

        if ($adminRoleId) {
            foreach ($permissions as $permissionName) {
                $permissionId = DB::table('permissions')
                    ->where('name', $permissionName)
                    ->value('id');

                if ($permissionId) {
                    $exists = DB::table('role_permissions')
                        ->where('permission_id', $permissionId)
                        ->where('role_id', $adminRoleId)
                        ->exists();

                    if (! $exists) {
                        DB::table('role_permissions')->insert([
                            'permission_id' => $permissionId,
                            'role_id' => $adminRoleId,
                        ]);
                    }
                }
            }
        }
    }

    private function deletePermissions()
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $permissions = [
            'create-sales-quotations',
            'read-sales-quotations',
            'update-sales-quotations',
            'delete-sales-quotations',
        ];

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $permissions)
            ->pluck('id');

        DB::table('role_permissions')
            ->whereIn('permission_id', $permissionIds)
            ->delete();

        DB::table('permissions')
            ->whereIn('name', $permissions)
            ->delete();
    }
};
