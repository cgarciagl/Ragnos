<?php

namespace App\ThirdParty\Ragnos\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table = 'gen_audit_logs';
    protected $allowedFields = [
        'user_id',
        'table_name',
        'record_id',
        'action',
        'changes',
        'ip_address',
        'user_agent'
    ];
    protected $useTimestamps = false; // Lo maneja la BD
}