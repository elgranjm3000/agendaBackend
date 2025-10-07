<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // job_client_status
        Schema::create('job_client_status', function (Blueprint $table) {
            $table->bigIncrements('id_status');
            $table->string('descrip', 100);
            $table->boolean('is_life');
        });

        // job_client_status_contact
        Schema::create('job_client_status_contact', function (Blueprint $table) {
            $table->bigIncrements('id_contact');
            $table->string('descrip', 100);
            $table->smallInteger('id_status');
            $table->boolean('is_life');
            $table->boolean('is_scheduled');
        });

        // job_offers
        Schema::create('job_offers', function (Blueprint $table) {
            $table->bigIncrements('id_offers');
            $table->string('descrip', 200);
            $table->date('date_begin');
            $table->date('date_end');
            $table->timestamp('stamp')->useCurrent();
            $table->smallInteger('id_user');
            $table->boolean('is_life');
            $table->boolean('is_delete');
        });

        // job_phone
        Schema::create('job_phone', function (Blueprint $table) {
            $table->bigIncrements('id_phone');
            $table->integer('id_client');
            $table->string('attrib1', 50)->default('');
            $table->string('attrib2', 50)->default('');
            $table->string('attrib3', 50)->default('');
            $table->string('attrib4', 50)->default('');
            $table->string('attrib5', 50)->default('');
            $table->integer('phone');
            $table->date('update_date');

            $table->index('id_client');
        });

        // job_day_executive
        Schema::create('job_day_executive', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('id_offers')->default(0);
            $table->integer('id_client');
            $table->char('dv_client', 1);
            $table->integer('id_executive');
            $table->char('dv_executive', 1);
            $table->string('name', 50);
            $table->string('last_name1', 50);
            $table->string('last_name2', 50);
            $table->string('id_office', 8)->default('');
            
            // Attributes 1-22
            $table->string('attrib1', 50)->default('');
            $table->string('attrib2', 50)->default('');
            $table->string('attrib3', 50)->default('');
            $table->string('attrib4', 50)->default('');
            $table->string('attrib5', 50)->default('');
            $table->string('attrib6', 50)->default('');
            $table->string('attrib7', 50)->default('');
            $table->string('attrib8', 50)->default('');
            $table->string('attrib9', 50)->default('');
            $table->string('attrib10', 50)->default('');
            $table->string('attrib11', 50)->default('');
            $table->string('attrib12', 50)->default('');
            $table->string('attrib13', 50)->default('');
            $table->string('attrib14', 50)->default('');
            $table->string('attrib15', 50)->default('');
            $table->string('attrib16', 50)->default('');
            $table->string('attrib17', 50)->default('');
            $table->string('attrib18', 50)->default('');
            $table->string('attrib19', 50)->default('');
            $table->string('attrib20', 50)->default('');
            $table->string('attrib21', 50)->default('');
            $table->string('attrib22', 50)->default('');
            
            $table->smallInteger('id_status')->default(1);
            $table->smallInteger('id_contact')->default(0);
            $table->dateTime('scheduled_date')->nullable();
            $table->timestamp('stamp')->useCurrent();
        });

        // job_day_contact
        Schema::create('job_day_contact', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('id_offers')->default(0);
            $table->bigInteger('id_phone');
            $table->integer('id_client');
            $table->timestamp('stamp')->useCurrent();
            $table->integer('id_executive');
            $table->smallInteger('id_status');
            $table->smallInteger('id_contact');
            $table->dateTime('scheduled_date')->nullable();

            $table->index('id_client');
            $table->index('id_phone');
        });

        // job_indicators_executive
        Schema::create('job_indicators_executive', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('type')->default(0);
            $table->string('period', 6)->default('');
            $table->integer('id_executive');
            $table->string('title', 100);
            $table->float('amount');
            $table->string('maskAmount', 25);
            $table->string('footer', 100);
            $table->string('title_color', 20);
            $table->string('y1', 200)->nullable();
            $table->string('x1', 200)->nullable();
            $table->string('y2', 200)->nullable();
            $table->string('x2', 200)->nullable();

            $table->unique(['type', 'period', 'id_executive'], 'type_indicators');
        });

        // job_attrib
        Schema::create('job_attrib', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('id_type');
            
            // Attributes 1-22
            $table->string('attrib1', 50)->default('');
            $table->string('attrib2', 50)->default('');
            $table->string('attrib3', 50)->default('');
            $table->string('attrib4', 50)->default('');
            $table->string('attrib5', 50)->default('');
            $table->string('attrib6', 50)->default('');
            $table->string('attrib7', 50)->default('');
            $table->string('attrib8', 50)->default('');
            $table->string('attrib9', 50)->default('');
            $table->string('attrib10', 50)->default('');
            $table->string('attrib11', 50)->default('');
            $table->string('attrib12', 50)->default('');
            $table->string('attrib13', 50)->default('');
            $table->string('attrib14', 50)->default('');
            $table->string('attrib15', 50)->default('');
            $table->string('attrib16', 50)->default('');
            $table->string('attrib17', 50)->default('');
            $table->string('attrib18', 50)->default('');
            $table->string('attrib19', 50)->default('');
            $table->string('attrib20', 50)->default('');
            $table->string('attrib21', 50)->default('');
            $table->string('attrib22', 50)->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_day_contact');
        Schema::dropIfExists('job_day_executive');
        Schema::dropIfExists('job_indicators_executive');
        Schema::dropIfExists('job_phone');
        Schema::dropIfExists('job_offers');
        Schema::dropIfExists('job_client_status_contact');
        Schema::dropIfExists('job_client_status');
        Schema::dropIfExists('job_attrib');
    }
};