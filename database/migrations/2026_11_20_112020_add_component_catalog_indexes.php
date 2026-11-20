
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->index('name', 'components_name_idx');
            $table->index(
                ['is_active', 'stock'],
                'components_availability_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropIndex('components_name_idx');
            $table->dropIndex('components_availability_idx');
        });
    }
};
