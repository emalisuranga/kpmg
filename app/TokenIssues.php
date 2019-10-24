<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class TokenIssues extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'token_issues';

    protected $primaryKey = 'email';

    protected $fillable = [
        'email', 'token', 'token_type'
    ];
}
