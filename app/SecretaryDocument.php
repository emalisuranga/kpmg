<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Secretary;
use Documents;
use OwenIt\Auditing\Contracts\Auditable;
class SecretaryDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'secretary_documents';

    public function Secretary()
    {
        return $this->belongsTo('App\Secretary');
    }


    public function Documents()
    {
        return $this->hasMany('App\Documents');
    }

}
