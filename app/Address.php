<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Secretary;
use Auditor;
use OwenIt\Auditing\Contracts\Auditable;
class Address extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'addresses';

    protected $fillable = [
        'address1','address2','city','district','province','gn_division','country','postcode'
    ];

    public function Secretary()
    {
        return $this->belongsTo('App\Secretary');
    }
    public function Auditor()
    {
        return $this->belongsTo('App\Auditor');
    }

}
