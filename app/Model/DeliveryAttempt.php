<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class DeliveryAttempt extends Model
{
    // This table is for tracking delivery attempts by Email object to a given subscriber
	// Notice that the actually delivery result is tracked in the TrackingLog table
	// Why this table?
	// It is because tracking_logs records are produced only after actual delivery, and updating a log table is not recommended
	// So we have:
	//
	//               Trigger
	//                  |
	//                  |
	//         [ Email, Subscriber ]           
	//                  |
	//                  |
	//           DeliveryAttempt ---- SystemJob ----> Job
	//                  |
	//                  |
	//             TrackingLog

	protected $fillable = [
		'subscriber_id', 'email_id', 'auto_trigger_id',
	];
}
