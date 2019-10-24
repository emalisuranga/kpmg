<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use AuditorFirm;
use OwenIt\Auditing\Contracts\Auditable;
class AuditorFirmPartner extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'auditor_firm_partners';
    public $timestamps = false;

    public function AuditorFirm()
    {
        return $this->hasMany('App\AuditorFirm');
    }

}
