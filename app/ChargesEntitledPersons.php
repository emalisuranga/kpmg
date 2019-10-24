<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class ChargesEntitledPersons extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'charges_entitled_persons';
    public $timestamps = false;
}
