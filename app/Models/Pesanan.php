<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan';

    // protected $fillable = ['no_order','no_receip','meja_id','nama_pelanggan','status','checkout'];
    protected $guarded = ['id'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->timestamps = false;
            $model->created_at = now();
        });
        static::updating(function ($model) {
            $model->timestamps = true;
        });
    }

    function get_field($request){
        // return $this->fillable;
        $data = [];
        foreach ($request->data as $key => $value) {
            if (in_array($key,$this->fillable)) {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    public function getUpdatedAtAttribute()
    {
    return \Carbon\Carbon::parse($this->attributes['updated_at'])->format('Y-m-d H:i:s');
    }

    public function getCreatedAtAttribute()
    {
    return \Carbon\Carbon::parse($this->attributes['created_at'])->format('Y-m-d H:i:s');
    }

    function get_pesanan($id = null)
    {

        $sql = "select a.*, b.no_meja
                ,(
                    case when a.created_by is null then '-'
                    else c.name
                    end
                ) created_by_username
                from $this->table a
                left join meja b on b.id = a.meja_id
                left join users c on a.created_by = c.id
                ";
        if (strlen($id) > 0) {
            $sql .= "where a.id = $id ";
        }
        $sql .= "order by a.created_at desc";
        
        $data = json_decode(json_encode(DB::select($sql)), true);


        for ($i=0; $i <count($data); $i++) { 
            $data[$i]['kode_unik'] = $this->encrypt_decrypt('encrypt', $data[$i]['id']);
            $data[$i]['order_detail'] = $this->get_all_detail($data[$i]['id']);
        }


        return $data;

    }

    function create_no_pesanan()
    {
        $date =  \date('Y-m-d');
        $last_pesanan = DB::table('pesanan')->whereRaw("cast(created_at as date) = current_date")->orderBy('created_at', 'desc')->first();
        if ($last_pesanan) {
            return sprintf("%03d", (Int)$last_pesanan->no_order + 1);
        } else {
            return "001";
        }
    }

    function get_all_detail($id)
    {
        $sql = "select a.id, a.harga, a.sub_harga, a.qty, a.menu_id, b.nama_menu, b.image, a.name_attribute, a.status, a.keterangan, a.created_at, a.updated_at
                from pesanan_detail a
                left join menu b on a.menu_id = b.id
                where a.pesanan_id = :pesanan_id
        ";
        return json_decode(json_encode(DB::select($sql, ['pesanan_id' => $id])), true);
    }
    
    private function total_pengunjung()
    {
        $sql = "
        SELECT count(*) as total_pengunjung FROM pesanan a
        WHERE Month(a.created_at) = Month(CURRENT_DATE) and Year(a.created_at) = Year(CURRENT_DATE)
        and status = 2
        ";
        return json_decode(json_encode(DB::select($sql)), true);
    }
    
    private function pendapatan_harian()
    {
        $sqlx = "
        SELECT COALESCE(sum(a.total_harga), 0) as pendapatan_harian 
        FROM pesanan a
        WHERE a.status = 2
        AND cast(a.created_at as date) BETWEEN cast(NOW() - INTERVAL 1 month AS date) AND CURRENT_DATE
        ";
        $sql = "
        SELECT COALESCE(sum(a.total_harga), 0) as pendapatan_harian 
        FROM pesanan a
        WHERE a.status = 2
        and Month(a.created_at) = Month(CURRENT_DATE) and Year(a.created_at) = Year(CURRENT_DATE)
        ";
        return json_decode(json_encode(DB::select($sql)), true);
        // return $sql;
    }
    
    private function menu_terjual_harian()
    {
        $sqlx = "
        SELECT coalesce(sum(qty), 0) as menu_terjual_harian 
        FROM pesanan a
        LEFT JOIN  pesanan_detail b on b.pesanan_id = a.id and b.status = 2
        WHERE cast(a.created_at as date) BETWEEN cast(NOW() - INTERVAL 1 day AS date) AND CURRENT_DATE
        ";
        $sql = "
        SELECT coalesce(sum(qty), 0) as menu_terjual_harian 
        FROM pesanan a
        LEFT JOIN  pesanan_detail b on b.pesanan_id = a.id and b.status = 2
        WHERE Month(a.created_at) = Month(CURRENT_DATE) and Year(a.created_at) = Year(CURRENT_DATE)
        ";
        return json_decode(json_encode(DB::select($sql)), true);
    }
    
    private function penjualan_bulanan()
    {
        $sql = "
        SELECT coalesce(sum(a.total_harga),0) as penjualan_bulanan 
        FROM pesanan a
        WHERE a.status = 1 and cast(a.created_at as date) BETWEEN cast(NOW() - INTERVAL 1 month AS date) AND CURRENT_DATE
        ";
        return json_decode(json_encode(DB::select($sql)), true);
    }
    
    private function top_menu()
    {
        $sqlx = "
        select nama_menu, attribute, jumlah
        from (
            select max(a.nama_menu) as nama_menu
            , max(b.name_attribute) as attribute
            , sum(qty) as jumlah
            from menu a
            left join pesanan_detail b on b.menu_id = a.id and b.status = 1
            WHERE cast(b.created_at as date) BETWEEN cast(NOW() - INTERVAL 30 DAY AS date) AND CURRENT_DATE
            
            group by a.id, b.name_attribute
            order by a.id
        ) as a
        order by a.jumlah desc 
        limit 5
        ";
        $sql = "
        select nama_menu, attribute, harga, image, jumlah
        from (
            select max(a.nama_menu) as nama_menu
            , coalesce(max(b.name_attribute), '-') as attribute
            , max(a.harga) +  coalesce(max(c.harga),0) as harga
            , max(a.image) as image
            , sum(qty) as jumlah
            from menu a
            left join attribute c on a.id = c.menu_id
            left join pesanan_detail b on b.menu_id = a.id and b.status = 2
            WHERE Month(b.created_at) = Month(CURRENT_DATE) and Year(b.created_at) = Year(CURRENT_DATE)
            group by a.id, b.name_attribute
            order by a.id
        ) as a
        order by a.jumlah desc 
        limit 5
        ";

        return json_decode(json_encode(DB::select($sql)), true);
    }

    function pendapatan_per_bulan($tahun = null)
    {
        $tahun = $tahun ?? date('Y'); 

        for ($i=1; $i <= 12; $i++) { 
            $sql = "
                SELECT coalesce(sum(a.total_harga),0) as pendapatan_bulanan 
                FROM pesanan a
                WHERE a.status = 2 and Month(a.created_at) = $i and Year(a.created_at) = $tahun
            ";

            $bulan[$i] = json_decode(json_encode(DB::select($sql)), true)[0]['pendapatan_bulanan'];
        }

        $return = [
            'tahun' => $tahun,
            'value' => $bulan
        ];

        return $return;

    }
    
    function grafik_dishes_selled($tahun = null)
    {
        $tahun = $tahun ?? date('Y'); 

        for ($i=1; $i <= 12; $i++) { 
            $sql = "
            SELECT coalesce(sum(qty), 0) as menu_terjual_harian 
            FROM pesanan a
            LEFT JOIN  pesanan_detail b on b.pesanan_id = a.id and b.status = 2
            WHERE a.status = 2 and Month(a.created_at) = $i and Year(a.created_at) = $tahun
            ";

            $bulan[$i] = json_decode(json_encode(DB::select($sql)), true)[0]['menu_terjual_harian'];
        }

        
        $return = [
            'tahun' => $tahun,
            'value' => $bulan
        ];

        return $return;
    }
    
    function grafik_table_used($tahun = null)
    {
        $tahun = $tahun ?? date('Y'); 

        for ($i=1; $i <= 12; $i++) { 
            $sql = "
            SELECT count(*) as total_pengunjung FROM pesanan a
            WHERE a.status = 2 and Month(a.created_at) = $i and Year(a.created_at) = $tahun
            ";

            $bulan[$i] = json_decode(json_encode(DB::select($sql)), true)[0]['total_pengunjung'];
        }

        
        $return = [
            'tahun' => $tahun,
            'value' => $bulan
        ];

        return $return;
    }

    function get_dashboard($tahun = null)
    {
        $tahun = $tahun ?? \date('Y');
        $data = [
            'table_used' => $this->total_pengunjung()[0]['total_pengunjung'],
            'pendapatan_harian' => $this->pendapatan_harian()[0]['pendapatan_harian'],
            'dishes_selled' => $this->menu_terjual_harian()[0]['menu_terjual_harian'],
            'top_menu' => $this->top_menu(),
            'grafik_pendapatan_bulanan' => $this->pendapatan_per_bulan($tahun),
            'grafik_dishes_selled' => $this->grafik_dishes_selled($tahun),
            'grafik_table_used' => $this->grafik_table_used($tahun),
        ];
        return $data;
    }

    function get_menu_kitchen()
    {
        $sql = "select a.id, a.no_order, b.no_meja, a.status, a.created_at, a.updated_at
                from $this->table a
                left join meja b on b.id = a.meja_id
                where status = 0 
                and a.updated_at is not null
                and cast(a.created_at as date) = current_date
                ";
        $sql .= "order by a.updated_at asc";
        
        $data = json_decode(json_encode(DB::select($sql)), true);


        for ($i=0; $i <count($data); $i++) {
            $sql = "
                    select a.id, a.qty, b.nama_menu, b.image, a.name_attribute, a.status, a.keterangan, a.created_at, a.updated_at
                    from pesanan_detail a
                    left join menu b on a.menu_id = b.id
                    where a.pesanan_id = :pesanan_id
                    ";
            $data[$i]['order_detail'] = json_decode(json_encode(DB::select($sql, ['pesanan_id' => $data[$i]['id']])), true);;
        }

        return $data;
    }
    
    function get_history_order($id = null)
    {
        $sql = "select a.id
                        ,a.no_order
                        ,a.no_receip
                        ,b.no_meja
                        ,a.nama_pelanggan
                        ,a.payment_type
                        ,a.pajak
                        ,a.total_harga
                        ,a.sub_total
                        ,a.bayar
                        ,a.kembalian
                        ,a.status
                        ,a.checkout
                        ,a.created_by
                        ,(
                            case when a.created_by is null then '-'
                            else c.name
                            end
                        ) created_by_username
                        ,a.updated_at
                        ,a.created_at
                from $this->table a
                left join meja b on b.id = a.meja_id
                left join users c on a.created_by = c.id
                ";
        if (strlen($id) > 0) {
            $sql .= "where a.id = $id ";
        }
        $sql .= "order by a.created_at desc";
        
        $data = json_decode(json_encode(DB::select($sql)), true);


        for ($i=0; $i <count($data); $i++) {
            $sql = "
                    select a.id, a.qty, b.nama_menu, b.image, a.name_attribute, a.status, a.created_at, a.updated_at
                    from pesanan_detail a
                    left join menu b on a.menu_id = b.id
                    where a.pesanan_id = :pesanan_id
                    and a.status = 1
                    ";
            $data[$i]['order_detail'] = json_decode(json_encode(DB::select($sql, ['pesanan_id' => $data[$i]['id']])), true);;
        }

        return $data;
    }

    function encrypt_decrypt($action, $string){
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'key_one';
        $secret_iv = 'key_two';
        // hash
        $key = hash('sha256', $secret_key);
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if( $action == 'decrypt' ) {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
}
        