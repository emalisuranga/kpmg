<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class CompanyPublicRequestDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'company_public_requests_documents';
}
