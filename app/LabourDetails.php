<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class LabourDetails extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'company_labour_details';
}
