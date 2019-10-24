<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class SocietyBulkUploadFeeds extends Model  implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public $timestamps = false;

    protected $table = 'society_bulk_upload_docs';
}
