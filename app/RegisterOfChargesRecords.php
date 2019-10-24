<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class RegisterOfChargesRecords extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'register_of_charges_records';
    public $timestamps = false;
}
