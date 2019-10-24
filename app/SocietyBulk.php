<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class SocietyBulk extends Model  implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public $timestamps = false;

    protected $table = 'society_bulk';
}
