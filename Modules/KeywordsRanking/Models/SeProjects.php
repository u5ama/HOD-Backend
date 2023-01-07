<?php

namespace Modules\KeywordsRanking\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\CRM\Models\ReviewRequest;
use Modules\User\Models\User;

class SeProjects extends Model
{
    protected $table = 'users_project';
    protected $fillable = ['user_id', 'project_name', 'project_url', 'project_id'];

    public function localKeywords()
    {
        return $this->hasMany(LocalKeyword::class, 'user_id', 'user_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'id', 'user_id');
    }
}
