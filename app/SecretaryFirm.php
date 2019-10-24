<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Address;
use SecretaryFirmPartner;
use OwenIt\Auditing\Contracts\Auditable;
class SecretaryFirm extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'secretary_firm';

    public function Address()
    {
        return $this->belongsTo('App\Address');
    }

    
    public function SecretaryFirmPartner()
    {
        return $this->hasMany('App\SecretaryFirmPartner');
    }

    
}
