<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class TenderDocumentStatus extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'tender_documents_status';
}
