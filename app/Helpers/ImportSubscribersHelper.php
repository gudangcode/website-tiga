<?php

namespace Acelle\Helpers;

class ImportSubscribersHelper
{
    /**
     * Get message for status of importing job.
     *
     * @return string
     */
    public static function getMessage($job)
    {
        $data = json_decode($job->data);

        if ($data->status == 'failed') {
            $message = trans('messages.import_failed_message', ['error' => $data->error_message]);
        } elseif ($job->isNew() && !$job->isCancelled()) {
            $message = trans('messages.starting');
        } elseif ($job->isCancelled()) {
            $message = trans('messages.cancelled');
        } else {
            $message = trans('messages.import_export_statistics_line', [
                'total' => $data->total,
                'processed' => $data->processed,
                'success' => $data->processed,
                'error' => 0,
            ]);

            if (property_exists($data, "error_message")) {
                $message .= '<br/><span style="color:red">'.$data->error_message.'</span>';
            }
        }

        return $message;
    }
}
