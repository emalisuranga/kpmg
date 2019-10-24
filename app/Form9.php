<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class Form9 extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'company_share_form9';
    public $timestamps = false;
}
