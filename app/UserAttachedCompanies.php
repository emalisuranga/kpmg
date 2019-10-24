<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class UserAttachedCompanies extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'login_user_attached_companies';
    public $timestamps = false;
}
