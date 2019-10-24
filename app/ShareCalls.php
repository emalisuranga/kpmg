<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class ShareCalls extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'calls_on_shares';
    public $timestamps = false;
}
