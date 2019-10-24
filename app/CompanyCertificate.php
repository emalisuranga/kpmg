<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class CompanyCertificate extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'company_certificate';
}
