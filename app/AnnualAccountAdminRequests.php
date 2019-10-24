<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class AnnualAccountAdminRequests extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = 'admin_annual_account_requests';

}
