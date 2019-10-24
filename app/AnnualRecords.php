<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class AnnualRecords extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'annual_records';
    public $timestamps = false;
}
