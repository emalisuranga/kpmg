<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class GNDivision extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'gn_divisions';
}
