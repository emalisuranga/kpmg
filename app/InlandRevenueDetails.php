<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class InlandRevenueDetails extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'company_inland_revenue_details';
}
