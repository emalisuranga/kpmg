<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class IrdBusinessActivityCodes extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'ird_business_activity_code';
    public $timestamps = false;
}
