<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class RegisterOfCharges extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'register_of_charges';
    public $timestamps = false;
}
