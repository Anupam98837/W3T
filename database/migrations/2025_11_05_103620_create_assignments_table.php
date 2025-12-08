<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
 
return new class extends Migration
{
    private function isMysql(): bool
    {
        return Schema::getConnection()->getDriverName() === 'mysql';
    }
 
    private function tryAddFk(string $table, string $column, string $refTable, string $fkName, $onDelete = 'cascade', $onUpdate = 'cascade'): void
    {
        if (!Schema::hasTable($table) || !Schema::hasTable($refTable) || !Schema::hasColumn($table, $column)) {
            return;
        }
        try {
            Schema::table($table, function (Blueprint $tb) use ($column, $refTable, $fkName, $onDelete, $onUpdate) {
                $fb = $tb->foreign($column, $fkName)->references('id')->on($refTable);
                $onDelete === 'set null' ? $fb->nullOnDelete() : $fb->onDelete($onDelete);
                $fb->onUpdate($onUpdate);
            });
        } catch (\Throwable $e) { /* ignore to keep non-breaking */ }
    }
 
    public function up(): void
    {
        if (!Schema::hasTable('assignments')) {
            Schema::create('assignments', function (Blueprint $table) {
                if ($this->isMysql()) $table->engine = 'InnoDB';
 
                $table->bigIncrements('id');
                $table->char('uuid', 36)->unique();
 
                // Parents (use big ints; modern Laravel defaults)
                $table->foreignId('course_id')->nullable();
                $table->foreignId('course_module_id')->nullable();
                $table->foreignId('batch_id')->nullable();
 
                // Core fields
                $table->string('title', 200);
                $table->longText('instruction')->nullable();
 
                $table->string('submission_type', 40)->nullable();     // 'text','file','link','repo', etc.
                $table->json('allowed_submission_types')->nullable();   // JSON: ["file","link"]
 
                $table->json('attachments_json')->nullable();           // any starter files
                $table->timestamp('due_at')->nullable();
                $table->integer('late_penalty_percent')->default(0);
 
                $table->string('status', 20)->default('draft')->index(); // draft|published|closed
 
                $table->foreignId('created_by')->nullable();            // users.id
                $table->json('metadata')->nullable();                   // extensibility
 
                $table->timestamps();
                $table->softDeletes();
            });
        }
 
        // Safe FK add (won't fail if parents not ready yet)
        $this->tryAddFk('assignments', 'course_id',        'courses',         'fk_assign_course',        'cascade', 'cascade');
        $this->tryAddFk('assignments', 'course_module_id', 'course_modules',  'fk_assign_course_module', 'cascade', 'cascade');
        $this->tryAddFk('assignments', 'batch_id',         'batches',         'fk_assign_batch',         'cascade', 'cascade');
        $this->tryAddFk('assignments', 'created_by',       'users',           'fk_assign_created_by',    'set null','cascade');
    }
 
    public function down(): void
    {
        if (!Schema::hasTable('assignments')) return;
 
        foreach ([
            'fk_assign_course',
            'fk_assign_course_module',
            'fk_assign_batch',
            'fk_assign_created_by',
        ] as $fk) {
            try {
                Schema::table('assignments', function (Blueprint $t) use ($fk) {
                    $t->dropForeign($fk);
                });
            } catch (\Throwable $e) { /* ignore */ }
        }
 
        Schema::dropIfExists('assignments');
    }
};