<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id(); // عمود ID تلقائي وزيادة تلقائية (Primary Key).
            $table->string('code')->unique(); // عمود كود الكوبون، يجب أن يكون نصًا فريدًا.
            $table->enum('type', ['fixed', 'percent']); // نوع الكوبون: إما ثابت القيمة أو نسبة مئوية.
            $table->decimal('value'); // قيمة الكوبون (رقم عشري).
            $table->decimal('cart_value'); // الحد الأدنى لقيمة السلة للاستفادة من الكوبون (رقم عشري).
            $table->date('expiry_date')->default(DB::raw("(DATE(CURRENT_TIMESTAMP))")); // تاريخ انتهاء الكوبون، القيمة الافتراضية هي تاريخ اليوم.
            $table->timestamps(); // عمودان تلقائيان: created_at و updated_at.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons');
    }
};
