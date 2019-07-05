<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
    	'id_user', 'path', 'icon', 'title', 'description'
    ];

	public function user()
	{
		return $this->belongsTo('App\User');
    }
}
