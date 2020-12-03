<?php

/**
 * Tool class.
 *
 * Misc helper tool
 *
 * LICENSE: This product includes software developed at
 * the Acelle Co., Ltd. (http://acellemail.com/).
 *
 * @category   Acelle Library
 *
 * @author     N. Pham <n.pham@acellemail.com>
 * @author     L. Pham <l.pham@acellemail.com>
 * @copyright  Acelle Co., Ltd
 * @license    Acelle Co., Ltd
 *
 * @version    1.0
 *
 * @link       http://acellemail.com
 */

namespace Acelle\Library;

class Tool
{
    /**
     * Copy a file, or recursively copy a folder and its contents.
     *
     * @param string $source      Source path
     * @param string $dest        Destination path
     * @param int    $permissions New folder creation permissions
     *
     * @return bool Returns true on success, false on failure
     */
    public static function xcopy($source, $dest, $permissions = 0755)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            $oldmask = umask(0);
            mkdir($dest, $permissions, true);
            umask($oldmask);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            self::xcopy("$source/$entry", "$dest/$entry", $permissions);
        }

        // Clean up
        $dir->close();

        return true;
    }

    /**
     * Delete a file, or recursively delete a folder and its contents.
     *
     * @param string $source Source path
     *
     * @return bool Returns true on success, false on failure
     */
    public static function xdelete($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (is_dir($dir.'/'.$object)) {
                        self::xdelete($dir.'/'.$object);
                    } else {
                        unlink($dir.'/'.$object);
                    }
                }
            }
            rmdir($dir);
        }

        return true;
    }

    /**
     * Get all time zone.
     *
     * @var array
     */
    public static function allTimeZones()
    {
        // Get all time zones with offset
        $zones_array = array();
        $timestamp = time();
        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones_array[$key]['zone'] = $zone;
            $zones_array[$key]['text'] = '(GMT'.date('P', $timestamp).') '.$zones_array[$key]['zone'];
            $zones_array[$key]['order'] = str_replace('-', '1', str_replace('+', '2', date('P', $timestamp))).$zone;
        }

        // sort by offset
        usort($zones_array, function ($a, $b) {
            return strcmp($a['order'], $b['order']);
        });

        return $zones_array;
    }

    /**
     * Get options array for select box.
     *
     * @var array
     */
    public static function getTimezoneSelectOptions()
    {
        $arr = [];
        foreach (self::allTimeZones() as $timezone) {
            $row = ['value' => $timezone['zone'], 'text' => $timezone['text']];
            $arr[] = $row;
        }

        return $arr;
    }

    /**
     * Format display datetime.
     *
     * @var string
     */
    public static function formatDateTime($datetime)
    {
        $result = self::dateTime($datetime)->format(trans('messages.datetime_format'));

        return $result;
    }

    /**
     * Format display datetime.
     *
     * @var string
     */
    public static function dateTime($datetime)
    {
        $timezone = self::currentTimezone();
        $result = $datetime;
        $result = $result->timezone($timezone);

        return $result;
    }

    /**
     * Format display datetime.
     *
     * @var string
     */
    public static function customerDateTime($datetime)
    {
        $timezone = is_object(\Auth::user()) && is_object(\Auth::user()->customer) ? \Auth::user()->customer->timezone : '';
        $result = $datetime;
        if (!empty($timezone)) {
            $result = $result->timezone($timezone);
        }

        return $result;
    }

    /**
     * Format display datetime.
     *
     * @var string
     */
    public static function dateTimeFromString($time_string)
    {
        return self::dateTime(\Carbon\Carbon::parse($time_string));
    }

    /**
     * For mat human time.
     *
     * @param       DateTime
     *
     * @return string
     */
    public static function formatHumanTime($time)
    {
        return $time->diffForHumans();
    }

    /**
     * Change singular to plural.
     *
     * @param       string
     *
     * @return string
     */
    public static function getPluralPrase($phrase, $value)
    {
        $plural = '';
        if ($value > 1) {
            for ($i = 0; $i < strlen($phrase); ++$i) {
                if ($i == strlen($phrase) - 1) {
                    $plural .= ($phrase[$i] == 'y' && $phrase != 'day') ? 'ies' : (($phrase[$i] == 's' || $phrase[$i] == 'x' || $phrase[$i] == 'z' || $phrase[$i] == 'ch' || $phrase[$i] == 'sh') ? $phrase[$i].'es' : $phrase[$i].'s');
                } else {
                    $plural .= $phrase[$i];
                }
            }

            return $plural;
        }

        return $phrase;
    }

    /**
     * Get file/folder permissions.
     *
     * @param       string
     *
     * @return string
     */
    public static function getPerms($path)
    {
        return substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * Get system time conversion.
     *
     * @param       string
     *
     * @return string
     */
    public static function systemTime($time)
    {
        return $time->setTimezone(config('app.timezone'));
    }

    /**
     * Get system time conversion.
     *
     * @param       string
     *
     * @return string
     */
    public static function systemTimeFromString($string)
    {
        $timezone = self::currentTimezone();
        $time = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $string, $timezone);
        $time = self::systemTime($time);

        return $time;
    }

    /**
     * Get bytes from string.
     *
     * @param string
     *
     * @return string
     */
    public static function returnBytes($val)
    {
        //$val = trim($val);
        //$last = strtolower($val[strlen($val)-1]);
        //switch($last)
        //{
        //    case 'g':
        //    $val *= 1024;
        //    case 'm':
        //    $val *= 1024;
        //    case 'k':
        //    $val *= 1024;
        //}
        return $val;
    }

    /**
     * Get max upload file.
     *
     * @param string
     *
     * @return string
     */
    public static function maxFileUploadInBytes()
    {
        //select maximum upload size
        $max_upload = self::returnBytes(ini_get('upload_max_filesize'));
        //select post limit
        $max_post = self::returnBytes(ini_get('post_max_size'));
        //select memory limit
        $memory_limit = self::returnBytes(ini_get('memory_limit'));
        // return the smallest of them, this defines the real limit
        return min($max_upload, $max_post);
    }

    /**
     * Day of week select options.
     *
     * @param string
     *
     * @return array
     */
    public static function dayOfWeekSelectOptions()
    {
        return [
            ['value' => '1', 'text' => trans('messages.Monday')],
            ['value' => '2', 'text' => trans('messages.Tuesday')],
            ['value' => '3', 'text' => trans('messages.Wednesday')],
            ['value' => '4', 'text' => trans('messages.Thursday')],
            ['value' => '5', 'text' => trans('messages.Friday')],
            ['value' => '6', 'text' => trans('messages.Saturday')],
            ['value' => '7', 'text' => trans('messages.Sunday')],
        ];
    }

    /**
     * Day of week arrays.
     *
     * @param string
     *
     * @return array
     */
    public static function weekdaysArray()
    {
        $array = [];
        foreach (self::dayOfWeekSelectOptions() as $day) {
            $array[$day['value']] = $day['text'];
        }

        return $array;
    }

    /**
     * Month select options.
     *
     * @param string
     *
     * @return array
     */
    public static function monthSelectOptions()
    {
        return [
            ['value' => '1', 'text' => trans('messages.January')],
            ['value' => '2', 'text' => trans('messages.February')],
            ['value' => '3', 'text' => trans('messages.March')],
            ['value' => '4', 'text' => trans('messages.April')],
            ['value' => '5', 'text' => trans('messages.May')],
            ['value' => '6', 'text' => trans('messages.June')],
            ['value' => '7', 'text' => trans('messages.July')],
            ['value' => '8', 'text' => trans('messages.August')],
            ['value' => '9', 'text' => trans('messages.September')],
            ['value' => '10', 'text' => trans('messages.October')],
            ['value' => '11', 'text' => trans('messages.November')],
            ['value' => '12', 'text' => trans('messages.December')],
        ];
    }

    /**
     * Month array.
     *
     * @param string
     *
     * @return array
     */
    public static function monthsArray()
    {
        $array = [];
        foreach (self::monthSelectOptions() as $day) {
            $array[$day['value']] = $day['text'];
        }

        return $array;
    }

    /**
     * Week select options.
     *
     * @param string
     *
     * @return array
     */
    public static function weekSelectOptions()
    {
        return [
            ['value' => '1', 'text' => trans('messages.1st_week')],
            ['value' => '2', 'text' => trans('messages.2nd_week')],
            ['value' => '3', 'text' => trans('messages.3rd_week')],
            ['value' => '4', 'text' => trans('messages.4th_week')],
            ['value' => '5', 'text' => trans('messages.5th_week')],
        ];
    }

    /**
     * Week array.
     *
     * @param string
     *
     * @return array
     */
    public static function weeksArray()
    {
        $array = [];
        foreach (self::weekSelectOptions() as $day) {
            $array[$day['value']] = $day['text'];
        }

        return $array;
    }

    /**
     * Month select options.
     *
     * @param string
     *
     * @return array
     */
    public static function dayOfMonthSelectOptions()
    {
        $arr = [];
        for ($i = 1; $i < 32; ++$i) {
            $arr[] = ['value' => $i, 'text' => $i];
        }

        return $arr;
    }

    /**
     * Get day string from timestamp.
     *
     * @param timestamp
     *
     * @return string
     */
    public static function dayStringFromTimestamp($timestamp)
    {
        if (isset($timestamp) && $timestamp != '0000-00-00 00:00:00') {
            // @todo: hard day format code: 'Y-m-d'
            $result = \Acelle\Library\Tool::dateTime($timestamp)->format('Y-m-d');
        } else {
            $result = \Acelle\Library\Tool::dateTime(\Carbon\Carbon::now())->format('Y-m-d');
        }

        return $result;
    }

    /**
     * Get time string from timestamp.
     *
     * @param timestamp
     *
     * @return string
     */
    public static function timeStringFromTimestamp($timestamp)
    {
        if (isset($timestamp) && $timestamp != '0000-00-00 00:00:00') {
            // @todo: hard day format code: 'H:i'
            $result = \Acelle\Library\Tool::dateTime($timestamp)->format('H:i');
        } else {
            $result = \Acelle\Library\Tool::dateTime(\Carbon\Carbon::now())->format('H:i');
        }

        return $result;
    }

    /**
     * Convert numbers array to weekdays array.
     *
     * @param timestamp
     *
     * @return string
     */
    public static function numberArrayToWeekdaysArray($numbers)
    {
        $weekdays_texts = self::weekdaysArray();
        $weekdays = [];
        foreach ($numbers as $number) {
            $weekdays[] = $weekdays_texts[$number];
        }

        return $weekdays;
    }

    /**
     * Convert numbers array to weeks array.
     *
     * @param timestamp
     *
     * @return string
     */
    public static function numberArrayToWeeksArray($numbers)
    {
        $weeks_texts = self::weeksArray();
        $weeks = [];
        foreach ($numbers as $number) {
            $weeks[] = $weeks_texts[$number];
        }

        return $weeks;
    }

    /**
     * Convert numbers array to months array.
     *
     * @param timestamp
     *
     * @return string
     */
    public static function numberArrayToMonthsArray($numbers)
    {
        $month_texts = self::monthsArray();
        $months = [];
        foreach ($numbers as $number) {
            $months[] = $month_texts[$number];
        }

        return $months;
    }

    /**
     * Get day names from array of numbers.
     *
     * @param timestamp
     *
     * @return string
     */
    public static function getDayNamesFromArrayOfNumber($numbers)
    {
        $names = [];

        $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
        foreach ($numbers as $number) {
            if (($number % 100) >= 11 && ($number % 100) <= 13) {
                $names[] = $number.'th';
            } else {
                $names[] = $number.$ends[$number % 10];
            }
        }

        return $names;
    }

    /**
     * Quota time unit options.
     *
     * @return array
     */
    public static function timeUnitOptions()
    {
        return [
            ['value' => 'minute', 'text' => trans('messages.minute')],
            ['value' => 'hour', 'text' => trans('messages.hour')],
            ['value' => 'day', 'text' => trans('messages.day')],
            ['value' => 'week', 'text' => trans('messages.week')],
            ['value' => 'month', 'text' => trans('messages.month')],
            ['value' => 'year', 'text' => trans('messages.year')],
        ];
    }

    /**
     * Get php paths select options.
     *
     * @param timestamp
     *
     * @return string
     */
    public static function phpPathsSelectOptions($paths)
    {
        $options = [];

        foreach ($paths as $path) {
            $options[] = [
                'text' => $path,
                'value' => $path,
            ];
        }

        $options[] = [
            'text' => trans('messages.php_bin_manual'),
            'value' => 'manual',
        ];

        return $options;
    }

    /**
     * Check php bin path is valid.
     *
     * @param string
     *
     * @return bool
     */
    public static function checkPHPBinPath($path)
    {
        $result = '';
        try {
            if (!file_exists($path) || !is_executable($path)) {
                return $result;
            }
        } catch (\Exception $ex) {
            // open_basedir in effect
        }

        if (exec_enabled()) {
            $exec_script = $path.' '.base_path().'/php_bin_test.php 2>&1';
            $result = exec($exec_script, $output);
        } else {
            $result = 'ok';
        }

        return $result;
    }

    /**
     * Get available System Background Methods Select Options.
     *
     * @param timestamp
     *
     * @return string
     */
    public static function availableSystemBackgroundMethodSelectOptions()
    {
        $options = [
            [
                'text' => trans('messages.database_job_type'),
                'value' => 'database',
                'description' => trans('messages.database_job_type_desc'),
            ],
        ];

        if (true) {
            $options[] = [
                'text' => trans('messages.async_job_type'),
                'description' => trans('messages.async_job_type_desc'),
                'value' => 'async',
                'disabled' => true, //exec_enabled(),
                'tooltip' => (!exec_enabled() ? 'Your server does not support async' : ''),
            ];
        }

        return $options;
    }

    /**
     * Control cronjob update request.
     *
     * @param timestamp
     *
     * @return string
     */
    public static function cronjobUpdateController($request, $controller)
    {

        // Suggestion paths
        $paths = [
            '/usr/local/bin/php',
            '/usr/bin/php',
            '/bin/php',
            '/usr/bin/php7',
            '/usr/bin/php7.0',
            '/usr/bin/php70',
            '/usr/bin/php7.1',
            '/usr/bin/php71',
            '/usr/bin/php56',
            '/usr/bin/php5.6',
            '/opt/plesk/php/5.6/bin/php',
            '/opt/plesk/php/7.0/bin/php',
            '/opt/plesk/php/7.1/bin/php',
        ];

        // try to detect system's PHP CLI
        if (exec_enabled()) {
            try {
                $paths = array_unique(array_merge($paths, explode(' ', exec('whereis php'))));
            } catch (\Exception $e) {
                // @todo: system logging here
                echo $e->getMessage();
            }
        }

        // validate detected / default PHP CLI
        // Because array_filter() preserves keys, you should consider the resulting array to be an associative array even if the original array had integer keys for there may be holes in your sequence of keys. This means that, for example, json_encode() will convert your result array into an object instead of an array. Call array_values() on the result array to guarantee json_encode() gives you an array.
        $paths = array_values(array_filter($paths, function ($path) {
            $checked = true;
            try {
                $checked = $checked && is_executable($path);
            } catch (\Exception $ex) {
                // in case of open_basedir, just throw skip it
                $checked = true;
            }

            return $checked && preg_match("/php[0-9\.a-z]{0,3}$/i", $path);
        }));

        $rules = [];

        // Current path
        $queue_driver = config('queue.default');
        $php_bin_path = empty($paths) ? 'manual' : $paths[0];
        $php_bin_path_value = empty($paths) ? '' : $paths[0];

        $setting_php_bin_path = \Acelle\Model\Setting::get('php_bin_path');
        if (!empty($setting_php_bin_path)) {
            if (in_array($setting_php_bin_path, $paths)) {
                $php_bin_path = $setting_php_bin_path;
            } else {
                $php_bin_path = 'manual';
            }
            $php_bin_path_value = $setting_php_bin_path;
        }

        if (!empty($request->old())) {
            $php_bin_path = $request->old()['php_bin_path'];
            $php_bin_path_value = $request->old()['php_bin_path_value'];
            $queue_driver = $request->old()['queue_driver'];
        }

        // create remote token if empty
        if (empty(\Acelle\Model\Setting::get('remote_job_token'))) {
            \Acelle\Model\Setting::set('remote_job_token', str_random(60));
        }

        $request->session()->forget('cron_jobs');
        $error = '';
        $valid = false;
        if ($request->isMethod('post')) {
            $php_bin_path = $request->php_bin_path;
            $php_bin_path_value = $request->php_bin_path_value;
            $queue_driver = $request->queue_driver;

            // If type == database
            if ($request->queue_driver == 'database') {
                $rules = [
                    'php_bin_path_value' => 'required',
                    'queue_driver' => 'required',
                ];

                // Check valid path
                $check = \Acelle\Library\Tool::checkPHPBinPath($php_bin_path_value);
                if ($check != 'ok') {
                    $rules['php_bin_path_invalid'] = 'required';
                }

                $controller->validate($request, $rules);

                \Acelle\Model\Setting::set('php_bin_path', $php_bin_path_value);

                $valid = true;
            }

            $request->session()->put('cron_jobs', true);

            // Update .env
            if (in_array($queue_driver, ['database', 'async']) && config('queue.default') != $queue_driver) {
                \Acelle\Model\Setting::setEnv('QUEUE_DRIVER', $queue_driver);
            }

            if ($request->queue_driver == 'async') {
                return 'done';
            }

            $request->session()->flash('alert-success', trans('messages.setting.updated'));
        }

        return [
            'step' => 5,
            'current' => 5,
            'php_paths' => $paths,
            'php_bin_path' => $php_bin_path,
            'php_bin_path_value' => $php_bin_path_value,
            'rules' => $rules,
            'error' => $error,
            'queue_driver' => $queue_driver,
            'valid' => $valid,
        ];
    }

    /**
     * Show re-captcha in views.
     *
     * @return string
     */
    public static function showReCaptcha($errors = null)
    {
        ?>
            <div class="recaptcha-box">
                <script src='https://www.google.com/recaptcha/api.js?hl=<?php echo language_code() ?>'></script>
                <div class="g-recaptcha" data-sitekey="6LfyISoTAAAAABJV8zycUZNLgd0sj-sBFjctzXKw"></div>
                <?php if (isset($errors) && $errors->has('recaptcha_invalid')) {
            ?>
                    <span class="help-block text-danger">
                        <strong><?php echo $errors->first('recaptcha_invalid'); ?></strong>
                    </span>
                <?php
        } ?>
            </div>
        <?php
    }

    /**
     * Check re-captcha success.
     *
     * @return bool
     */
    public static function checkReCaptcha($request)
    {
        if (!isset($request->all()['g-recaptcha-response'])) {
            return false;
        }

        // Check recaptch
        $client = new \GuzzleHttp\Client();
        $res = $client->post('https://www.google.com/recaptcha/api/siteverify', ['verify' => false, 'form_params' => [
            'secret' => '6LfyISoTAAAAAC0hJ916unwi0m_B0p7fAvCRK4Kp',
            'remoteip' => $request->ip(),
            'response' => $request->all()['g-recaptcha-response'],
        ]]);

        return json_decode($res->getBody(), true)['success'];
    }

    /**
     * Number select options.
     *
     * @param string
     *
     * @return array
     */
    public static function numberSelectOptions($min = 1, $max = 100)
    {
        $options = [];

        for ($i = $min; $i <= $max; ++$i) {
            $options[] = ['value' => $i, 'text' => $i];
        }

        return $options;
    }

    /**
     * Format price.
     *
     * @param string
     *
     * @return string
     */
    public static function format_price($price, $format = '{PRICE}')
    {
        return str_replace('{PRICE}', self::format_number($price), $format);
    }

    /**
     * Format price.
     *
     * @param string
     *
     * @return string
     */
    public static function format_number($number)
    {
        if (is_numeric($number) && floor($number) != $number) {
            return number_format($number, 2, trans('messages.dec_point'), trans('messages.thousands_sep'));
        } elseif (is_numeric($number)) {
            return number_format($number, 0, trans('messages.dec_point'), trans('messages.thousands_sep'));
        } else {
            return $number;
        }
    }

    /**
     * Format display date.
     *
     * @var string
     */
    public static function formatDate($datetime)
    {
        $result = !isset($datetime) ? '' : self::dateTime($datetime)->format(trans('messages.date_format'));

        return $result;
    }

    /**
     * Replace HTML url.
     *
     * @return text
     */
    public static function replaceTemplateUrl($content, $path)
    {
        // find all asset links in html content
        preg_match_all('/(?<=src=|background=|href=|url\()(\'|")?(?<url>.*?)(?=\1|\))/i', $content, $matches);
        $srcs = array_unique($matches['url']);

        foreach ($srcs as $src) {
            if (empty(trim($src))) {
                continue;
            }
            
            $new_src = str_replace('"', '', $src);
            $new_src = str_replace('\'', '', $new_src);
            $new_src = htmlspecialchars_decode($new_src); // Convert &amp; back to & (which was encoded by TinyMCE)

            if (preg_match('/https?/i', $src) || preg_match('/^\{/i', $src) || preg_match('/^\%/i', $src) || preg_match('/^https?/i', $src) || preg_match('/^#/i', $src) || strpos($src, '//') === 0 || preg_match('/^mailto:/i', $src) || preg_match('/^tel:/i', $src)) {
                // nothing
            } else {
                // Build the full path
                $new_src = $path.'/'.$new_src;
            }

            // using a quote in replacement to avoid replacing twice
            // for example
            //
            // dir/assets/2984023984320
            //    ==> http://$path/dir/assets/2984023984320
            // dir/assets
            //    ==> http://$path/http://$path/2984023984320
            // Example of quote: href="http://, href=http://, or url: (http://)

            try {
                // @important: the following line of code is very likely to get an "preg_replace(): Compilation failed: regular expression is too large at offset" error. That's the reason why we need try..catch here
                $content = preg_replace('/(src=|background=|href=|url\s*\()([\'"]?\s*)'.preg_quote($src, '/').'(\s*[\s\'"\)])/', "$1$2$new_src$3", $content);
            } catch (\ErrorException $ex) {
                // preg_replace(): Compilation failed: regular expression is too large at offset

                // use the old and non-safe way!!!!
                $content = str_replace($src, $new_src, $content);
            }
        }

        $content = str_replace('&quot;', '', $content);

        return $content;
    }

    /**
     * Check current view if exist.
     *
     * @return bool
     */
    public static function currentView()
    {
        return \Request::is('admin*') ? 'backend' : 'frontend';
    }

    /**
     * Get current timezone.
     *
     * @var string
     */
    public static function currentTimezone()
    {
        if (self::currentView() == 'frontend') {
            $timezone = is_object(\Auth::user()) && is_object(\Auth::user()->customer) ? \Auth::user()->customer->timezone : '+00:00';
        } elseif (self::currentView() == 'backend') {
            $timezone = is_object(\Auth::user()) && is_object(\Auth::user()->admin) ? \Auth::user()->admin->timezone : '+00:00';
        } else {
            $timezone = '+00:00';
        }

        return $timezone;
    }

    /**
     * Get Directory Size.
     *
     * @var string
     */
    public static function getDirectorySize($path)
    {
        $bytestotal = 0;
        $path = realpath($path);
        if ($path !== false) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $object) {
                $bytestotal += $object->getSize();
            }
        }

        return $bytestotal;
    }

    /**
     * Formats a line (passed as a fields  array) as CSV and returns the CSV as a string.
     * Adapted from http://us3.php.net/manual/en/function.fputcsv.php#87120.
     */
    public static function arrayToCsv(array &$fields, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false)
    {
        $delimiter_esc = preg_quote($delimiter, '/');
        $enclosure_esc = preg_quote($enclosure, '/');

        $output = array();
        foreach ($fields as $field) {
            if ($field === null && $nullToMysqlNull) {
                $output[] = 'NULL';
                continue;
            }

            // Enclose fields containing $delimiter, $enclosure or whitespace
            if ($encloseAll || preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field)) {
                $output[] = $enclosure.str_replace($enclosure, $enclosure.$enclosure, $field).$enclosure;
            } else {
                $output[] = $field;
            }
        }

        return implode($delimiter, $output);
    }

    /**
     * Check email is valid.
     *
     * @var string
     */
    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Get all timezones.
     *
     * @var array
     */
    public static function getFileType($filename)
    {
        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $arr = explode('.', $filename);
        $ext = strtolower(array_pop($arr));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);

            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }
}
