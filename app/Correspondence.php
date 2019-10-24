<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class Correspondence extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'correspondence';
    public $timestamps = false;
}
