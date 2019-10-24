<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use SecretaryDocument;
use AuditorDocument;
use OwenIt\Auditing\Contracts\Auditable;
class Documents extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'documents';
   
    public function SecretaryDocument()
    {
        return $this->hasMany('App\SecretaryDocument');
    }
    public function AuditorDocument()
    {
        return $this->hasMany('App\AuditorDocument');
    }

}
