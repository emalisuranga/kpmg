<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyItemChange extends Model
{
    protected $table = 'company_item_changes';

    protected $fillable = ['item_id', 'request_id', 'changes_type', 'item_table_type'];
}
