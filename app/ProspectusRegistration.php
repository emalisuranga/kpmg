<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class ProspectusRegistration extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'prospectus_registration';
    public $timestamps = false;
}
