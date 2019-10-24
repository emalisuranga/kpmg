<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class ShareIssueRecords extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'issue_of_share_items';
    public $timestamps = false;
}
