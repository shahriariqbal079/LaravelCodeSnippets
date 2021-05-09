<?php


protected $guarded = [];

public function user()
{
    return $this->belongsTo(User::class);
}

public function job()
{
    return $this->hasMany(Job::class);
}
