<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class CompanyMember extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'company_members';

    public function address()
    {
        return $this->hasOne('App\Address');
    }

}
