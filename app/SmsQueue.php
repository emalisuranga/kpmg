<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class SmsQueue extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'sms_queue';
    public $timestamps = false;
}
