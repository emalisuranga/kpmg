<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class ShareClasses extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'issue_of_shares';
    public $timestamps = false;
}
