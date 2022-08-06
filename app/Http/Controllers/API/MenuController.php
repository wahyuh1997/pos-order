<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuAtribute;
use App\Models\MenuKategori;
use Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Contracts\HasApiTokens;

class MenuController extends Controller
{
    function create_category(Request $request){
        
        $menuKategori = MenuKategori::create([
            'nama_kategori' => $request->nama_kategori
        ]);
        return $this->return_success('Data berhasil ditambahkan!', $menuKategori);
    }

    function get_category($id)
    {
        $menuKategori = MenuKategori::findOrfail($id);

        return $this->return_success('', $menuKategori);
    }

    function get_all_category()
    {
        $menuKategori = MenuKategori::all();

        return $this->return_success('', $menuKategori);
    }
    
    function delete_category($id)
    {
        $menuKategori = MenuKategori::findOrfail($id);
        
        $menuKategori->delete();
        
        return $this->return_success('data berhasil dihapus!',[]);
    }

    function update_category($id, Request $request)
    {
        $menuKategori = MenuKategori::findOrfail($id);
        
        $menuKategori->update([
            'nama_kategori' => $request->nama_kategori
        ]);
        
        return $this->return_success('data berhasil diubah!',$menuKategori);
    }

    function get_menu($id){
        $menu = new Menu();

        $get_menu = $menu->get_menu($id);

        if (count($get_menu) < 1) {
            return $this->return_failed('Data Tidak Ada');
        }

        return $this->return_success('', $get_menu[0]);
    }
    
    function get_all_menu(){
        $menu = new Menu();
        
        $get_menu = $menu->get_menu();
        
        return $this->return_success('', $get_menu);
    }

    function insert_menu(Request $request){
        // return $request;die;
        $menu = Menu::create([
            'nama_menu' => $request->nama_menu,
            'jenis' => $request->jenis,
            'kategori_id' => $request->kategori_id, 
            'harga' => $request->harga,
            'keterangan' => $request->keterangan,
            'image' => $request->image,
        ]);
    
        if ($request->attribute) {
            for ($i=0; $i < count($request->attribute) ; $i++) { 
                MenuAtribute::create([
                    'nama' => $request->attribute[$i]['nama'],
                    'menu_id' => $menu->id,
                    'harga' => $request->attribute[$i]['harga'],
                ]);
            }
            return $request;
        }
    
        $return = new Menu();
        $return = $return->get_menu($menu->id);
        return $this->return_success('Menu berhasil ditambahkan!', $return);
    }
    
    function edit_menu($id,Request $request){
        // return $request;die;
        $menu = Menu::findOrFail($request->id);

        $menu->update([
            'nama_menu' => $request->nama_menu,
            'jenis' => $request->jenis,
            'kategori_id' => $request->kategori_id, 
            'harga' => $request->harga,
            'keterangan' => $request->keterangan,
            'image' => $request->image,
        ]);
    
        if ($request->attribute) {
            for ($i=0; $i < count($request->attribute) ; $i++) {
                $attribute = $request->attribute[$i];

                $menuAtribut = MenuAtribute::where(['nama' =>$attribute['nama'], 'menu_id' => $menu->id])->first();
                if ($menuAtribut) {
                    $menuAtribut->update([
                        'harga' => $attribute['harga'],
                    ]);
                } else {
                    MenuAtribute::create([
                        'nama' => $attribute['nama'],
                        'menu_id' => $menu->id,
                        'harga' => $attribute['harga'],
                    ]);
                }
            }
        }
    
        $return = new Menu();
        $return = $return->get_menu($menu->id);
    
        return $this->return_success('Menu berhasil diubah!', $return);
    }

    function delete_attribute($id)
    {
        $menu = Menu::findOrfail($id);

        MenuAtribute::where('menu_id', $menu->id)->delete();
        
        $menu->delete();
        
        return $this->return_success('Menu berhasil dihapus!',[]);
    }
    
    function delete_attribute($nama)
    {
        MenuAtribute::where('nama', $nama)->delete();
        
        return $this->return_success('Attribute berhasil dihapus!',[]);
    }

    function get_name_attribute()
    {
        $data = [
            'kecil', 'besar'
        ];

        return $this->return_success('',$data);
    }
}
