<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class Rule extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'rules';

    protected $primaryKey = "id";

    public $timestamps = false;
}
