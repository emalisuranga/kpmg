<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class Charges extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'charges';
    public $timestamps = false;
}
