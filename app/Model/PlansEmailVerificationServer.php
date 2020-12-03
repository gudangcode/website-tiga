<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class PlansEmailVerificationServer extends Model
{
    // Plan status
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ACTIVE = 'active';

    public function emailVerificationServer()
    {
        return $this->belongsTo('Acelle\Model\EmailVerificationServer', 'server_id');
    }
}
