<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class ShareRegister extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'annual_share_register';
    public $timestamps = false;
}
