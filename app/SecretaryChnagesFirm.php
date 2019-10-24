<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class SecretaryChnagesFirm extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'secretary_firm_changes';
   // public $timestamps = false;
}
