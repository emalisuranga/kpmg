<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class TenderApplication extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'tender_applications';
}
