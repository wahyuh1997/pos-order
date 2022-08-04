<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuKategori;
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

    function update_category(Request $request)
    {
        $menuKategori = MenuKategori::findOrfail($request->id);
        
        $menuKategori->update([
            'nama_kategori' => $request->nama_kategori
        ]);
        
        return $this->return_success('data berhasil diubah!',$menuKategori);
    }

    function get_menu($id){
        $menu = new Menu();

        $get_menu = $menu->get_menu($id);

        return $this->return_success('', $get_menu[0]);
    }
    
    function get_all_menu(){
        $menu = new Menu();

        $get_menu = $menu->get_menu();

        return $this->return_success('', $get_menu);
    }
}
