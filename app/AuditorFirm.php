<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Address;
use AuditorFirmPartner;
use OwenIt\Auditing\Contracts\Auditable;
class AuditorFirm extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'auditor_firms';

    public function Address()
    {
        return $this->belongsTo('App\Address');
    }

    
    public function AuditorFirmPartner()
    {
        return $this->hasMany('App\AuditorFirmPartner');
    }

}
