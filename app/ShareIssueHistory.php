<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class ShareIssueHistory extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'issue_share_history';
    public $timestamps = false;
}
