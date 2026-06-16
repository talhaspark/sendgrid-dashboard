<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentEmail extends Model
{

protected $fillable=[
'sg_message_id',
'from_email',
'to_email',
'subject',
'status',
'sent_at',
'opens',
'clicks',
'bounces',
'blocks',
'deferred',
'spam_reports',
'unsubscribes',
'categories',
'custom_args',
'raw_payload'
];


protected $casts=[
'sent_at'=>'datetime',
'categories'=>'array',
'custom_args'=>'array',
'raw_payload'=>'array'
];

}
