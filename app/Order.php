<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class Order extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'orders';
}
