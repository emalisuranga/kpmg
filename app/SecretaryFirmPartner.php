<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use SecretaryFirm;
use OwenIt\Auditing\Contracts\Auditable;
class SecretaryFirmPartner extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'secretary_firm_partners';

    public $timestamps = false;

    public function SecretaryFirm()
    {
        return $this->hasMany('App\SecretaryFirm');
    }


}
