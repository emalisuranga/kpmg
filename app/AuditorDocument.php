<?php

namespace App;

use Auditor;
use Documents;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Model;

class AuditorDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'auditor_documents';

    public function Auditor()
    {
        return $this->belongsTo('App\Auditor');
    }

    public function Documents()
    {
        return $this->hasMany('App\Documents');
    }

}
