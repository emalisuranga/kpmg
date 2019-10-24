<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class SecDivision extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'sec_divisions';
    public $timestamps = false;
}
