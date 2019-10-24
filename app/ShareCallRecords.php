<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class ShareCallRecords extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'calls_on_shares_records';
    public $timestamps = false;
}
