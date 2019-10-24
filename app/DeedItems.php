<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class DeedItems extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'charges_deed_items';
    public $timestamps = false;
}
