<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class CompanySalutations extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'ird_company_salutations';
    public $timestamps = false;
}
