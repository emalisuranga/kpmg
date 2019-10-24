<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class OverseaseOfNameChange extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'name_change_notices_of_overseas';
    public $timestamps = false;
}
