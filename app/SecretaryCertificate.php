<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class SecretaryCertificate extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'secretary_certificates';

    protected $fillable = ['certificate_no', 'status', 'secretary_id'];
}
