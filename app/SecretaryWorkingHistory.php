<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Secretary;
use OwenIt\Auditing\Contracts\Auditable;
class SecretaryWorkingHistory extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'secretary_working_history';

    public $timestamps = false;

    public function Secretary()
    {
        return $this->belongsTo('App\Secretary');
    }
    
}
