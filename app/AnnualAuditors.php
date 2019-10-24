<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class AnnualAuditors extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'annual_auditors';
    public $timestamps = false;
}
