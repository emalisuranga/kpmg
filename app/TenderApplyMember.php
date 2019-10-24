<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class TenderApplyMember extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'tender_application_firm_members';
}
