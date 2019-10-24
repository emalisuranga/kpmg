<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OffshoreStrike extends Model
{
    protected $table = 'strike_off';
    public $timestamps = false;
    protected $primaryKey = "id";
}
