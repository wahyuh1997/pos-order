<?php

namespace Database\Seeders;

use App\Models\Meja;
use App\Models\Menu;
use App\Models\MenuAtribute;
use App\Models\MenuKategori;
use App\Models\Pesanan;
use App\Models\PesananDetail;
use Attribute;
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
            'image' => 'ini gambar',
            'keterangan' => 'minuman yang lembut dan menyegarkan'
        ]);

        $attribut = MenuAtribute::create([
            'nama' => 'reguler',
            'menu_id' => $menu->id,
            'harga' => 0,
        ]);

        $meja = Meja::create([
            'no_meja' => '1'
        ]);

        $pesanan = Pesanan::create([
            'no_order' => '001',
            'no_receip' => \date('Ymd').'001',
            'meja_id' => $meja->id,
            'nama_pelanggan' => 'fulan',
            'status' => 2,
            'checkout' => 1
        ]);

        $pesanan_detail = PesananDetail::create([
            'pesanan_id' => $pesanan->id
            , 'menu_id' => $menu->id
            , 'name_attribute' => $attribut->nama
            , 'status' => 1
            , 'harga' => $menu->harga
        ]);
    }
}
