<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class JvCompanies extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'tender_jv_companies';
    public $timestamps = false;
}
