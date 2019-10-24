<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuditorCertificate extends Model
{
    protected $table = 'auditor_certificates';

    protected $fillable = ['certificate_no', 'status', 'secretary_id'];
}
