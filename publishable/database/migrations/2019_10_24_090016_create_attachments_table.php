<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('attachment_type_id')->unsigned();
            $table->morphs('attachable');
            $table->string('original_file_name', 255);
            $table->string('extension', 100);
            $table->string('upload_path', 255)->nullable()->default(null);
            $table->string('mime_type', 100);
            $table->string('permission', 55)->nullable();
            $table->enum('status', ['Pending', 'Uploaded', 'Archived', 'Deleted'])->default('Pending');
            $table->integer('created_by')->unsigned()->nullable()->default(null);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->integer('deleted_by')->unsigned()->nullable()->default(null);
            $table->dateTime('deleted_at')->nullable()->default(null);
            $table->index(['attachment_type_id', 'attachable_type', 'attachable_id'], 'attachable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attachments');
    }
}
