<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Address;
use People;
use User;
use AuditorDocument;
use OwenIt\Auditing\Contracts\Auditable;
class Auditor extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'auditors';

    public function People()
    {
        return $this->belongsTo('App\People');
    }
    public function User()
    {
        return $this->belongsTo('App\User');
    }
    public function Address()
    {
        return $this->belongsTo('App\Address');
    }
    public function AuditorDocument()
    {
        return $this->hasMany('App\AuditorDocument');
    }











    
}
