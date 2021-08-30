<?php

namespace AskNicely\Model;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model {
    public $timestamps = false;
    protected $table = 'todos';
    protected $primaryKey = 'id';

    protected $attributes = [
        'description' => null,
        'done' => false,
    ];

    protected $fillable = [
        'description',
        'user_id'
    ];
}