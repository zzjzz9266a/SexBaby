<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Baby extends Model
{
    protected $fillable = ['member_id', 'title', 'connection', 'project', 'price', 'security', 'judge', 'detail', 'images', 'age', 'public_date', 'province', 'area', 'adress'];

    static function list($province)
    {
      if ($province) {
        $babies = Baby::where('valid', true)->where('province', $province)->select('id', 'member_id', 'title', 'price', 'public_date', 'images', 'area')->orderBy('public_date', 'desc')->paginate(20);
      }else{
        $babies = Baby::where('valid', true)->select('id', 'member_id', 'title', 'price', 'public_date', 'images', 'area')->orderBy('public_date', 'desc')->paginate(20);
      }
      return $babies;
    }

    static function detail($id)
    { 
      $baby = Baby::find($id);
      $baby->images = json_decode($baby->images);
      return $baby;
    }
}