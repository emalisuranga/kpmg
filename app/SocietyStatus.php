<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class SocietyStatus extends Model implements Auditable
{

    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'society_statuses';
}
