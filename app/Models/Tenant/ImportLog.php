<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $table = 'import_logs';
    public $timestamps = false;
    protected $fillable = ['entity_type', 'file_name', 'total_rows', 'success_count', 'error_count', 'duplicate_count', 'errors', 'duplicates', 'status', 'created_by'];
    protected $casts = ['errors' => 'json', 'duplicates' => 'json', 'created_at' => 'datetime'];
}
