<?php

namespace Modules\ThirdParty\Models;

use Illuminate\Database\Eloquent\Model;

class IssuesList extends Model
{
    protected $table = 'sys_issues';

    protected $primaryKey = 'issue_id';

    protected $fillable = ['title', 'site', 'module'];
}
