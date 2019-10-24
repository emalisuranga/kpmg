<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class AppointedAdmins extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'company_appointed_admins';
    public $timestamps = false;
}
