<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class SecretaryChnagesIndividual extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'secretary_changes';
   // public $timestamps = false;
}
