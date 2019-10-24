<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class IRDregPurposes extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'ird_purpose_of_registration';
    public $timestamps = false;
}
