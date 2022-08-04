<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use phpDocumentor\Reflection\Types\Nullable;

class MenuTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 255);
            $table->bigInteger('menu_id');
            $table->float('harga',20,2);
            $table->timestamps();
        });

        Schema::create('menu', function (Blueprint $table) {
            $table->id();
            $table->string('nama_menu');
            $table->enum('jenis',['makanan', 'minuman']);
            $table->bigInteger('kategori_id');
            $table->float('harga',20,2);
            $table->text('keterangan');
            $table->timestamps();
        });
        
        
        Schema::create('menu_kategori', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori', 255);
            $table->timestamps();
        });
        
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('no_pesanan');
            $table->bigInteger('meja_id')->nullable();
            $table->string('nama_pelanggan')->nullable();
            $table->integer('status')->default(0)->comment('0 = proses beli, 2 = proses kasir, 3 = dapur, 4 = selesai');
            $table->timestamps();
        });
        
        Schema::create('pesanan_detail', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pesanan_id');
            $table->bigInteger('menu_id');
            $table->float('harga',20,2);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('menu');
        Schema::drop('attribute');
        Schema::drop('pesanan');
        Schema::drop('pesanan_detail');
        Schema::drop('menu_kategori');
    }
}
