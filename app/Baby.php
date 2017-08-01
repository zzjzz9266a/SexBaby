<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Baby extends Model
{
    protected $fillable = ['member_id', 'title', 'connection', 'project', 'price', 'security', 'judge', 'detail', 'images', 'age', 'public_date', 'province', 'area', 'adress'];

    static function list($province)
    {
      return DB::table($province)->select('id', 'member_id', 'title', 'price', 'public_date')->orderBy('public_date', 'desc')->paginate(20);
    }

    static function detail($id, $province)
    { 
      $baby = DB::table($province)->where('id', $id)->limit(1)->get();
      return json_encode($baby[0]);
    }
}