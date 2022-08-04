<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuKategori;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu_kategori = MenuKategori::create([
            'nama_kategori' => 'pencuci mulut'
        ]);

        $menu = Menu::create([
            'nama_menu' => 'es krim',
            'jenis' => 'minuman',
            'kategori_id' => $menu_kategori->id, 
            'harga' => 5000,
            'keterangan' => 'minuman yang lembut dan menyegarkan'
        ]);

        DB::table('attribute')->insert([
            'nama' => 'reguler',
            'menu_id' => $menu->id,
            'harga' => 0,
        ]);
    }
}
