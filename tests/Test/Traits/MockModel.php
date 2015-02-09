<?php namespace C4tech\Test\Support\Test\Traits;

use C4tech\Support\Model;

class MockModel extends Model
{
    public function thing()
    {
        return $this->morphTo();
    }

    public function owner()
    {
        return $this->hasOne('User');
    }

    public function photos()
    {
        return $this->hasMany('Photo', 'photo_id');
    }

    public function users()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }

    public function posts()
    {
        return $this->hasManyThrough('Post', 'User', 'model_id', 'post_id');
    }

    public function photo()
    {
        return $this->morphOne('User', 'imageable', 'imageable_type', 'imageable_id', 'id');
    }

    public function scopeUserIs($query, $value)
    {
        return $query->where('left', '=', $value);
    }

    public function scopeUserIsnt($query, $value)
    {
        return $query->where('left', '<>', 'in ' . $value);
    }
}
