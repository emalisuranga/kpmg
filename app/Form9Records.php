<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class Form9Records extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'company_share_form9_records';
    public $timestamps = false;
}
