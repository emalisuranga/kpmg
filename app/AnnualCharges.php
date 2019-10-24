<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class AnnualCharges extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'annual_charges';
    public $timestamps = false;
}
