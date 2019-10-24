<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class Setting extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'settings';

    protected $primaryKey = "id";

    public $timestamps = false;

}
