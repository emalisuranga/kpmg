<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class Society extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'societies';

    protected $primaryKey = "id";
}
