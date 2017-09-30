<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Baby extends Model
{
    protected $fillable = ['member_id', 'title', 'connection', 'project', 'price', 'security', 'judge', 'detail', 'images', 'age', 'public_date', 'province', 'area', 'adress'];

    static function list($province)
    {
		if ($province) {
			$babies = Baby::where('valid', true)
            ->where('province', $province)
            // ->whereNotNull('images')->where('images', '<>', '[]')
            ->select('id', 'member_id', 'title', 'price', 'public_date', 'images', 'area')
            ->orderBy('public_date', 'desc')
            ->paginate(20);
		}else{
			$babies = Baby::where('valid', true)->select('id', 'member_id', 'title', 'price', 'public_date', 'images', 'area')->orderBy('public_date', 'desc')->paginate(20);
		}
		foreach ($babies as $baby) {
			$baby->images = json_decode($baby->images);
		}
		return $babies;
    }

    static function detail($id)
    { 
    	$baby = Baby::find($id);
    	$baby->images = json_decode($baby->images);
    	return $baby;
    }

    static function collection($ids)
    {
    	$ids = json_decode($ids);
    	// $babies = Baby::where('valid', true)->whereIn('id', $ids)->select('id', 'member_id', 'title', 'price', 'public_date', 'images', 'area')->paginate(20);
        $babies = array();
    	foreach ($ids as $id) {
            $baby = Baby::select('id', 'member_id', 'title', 'price', 'public_date', 'images', 'area')->find($id);
			$baby->images = json_decode($baby->images);
            $babies[] = $baby;
    	}
    	return ['data' => $babies];
    }

    static function search($province, $keyword)
    {
    	$babies = Baby::where('valid', true)
    	->where('province', $province)
    	->where(function ($query) use ($keyword)
    	{
    		$query->where('title', 'like', "%$keyword%")
    			->orWhere('connection', 'like', "%$keyword%")
		    	->orWhere('project', 'like', "%$keyword%")
		    	->orWhere('price', 'like', "%$keyword%")
		    	->orWhere('judge', 'like', "%$keyword%")
		    	->orWhere('area', 'like', "%$keyword%")
		    	->orWhere('address', 'like', "%$keyword%");
    	})
    	->select('id', 'member_id', 'title', 'price', 'public_date', 'images', 'area')
    	->orderBy('public_date', 'desc')
    	->paginate(20);
    	foreach ($babies as $baby) {
			$baby->images = json_decode($baby->images);
    	}
    	return $babies;
    }
}