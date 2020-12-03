<?php

/**
 * Template class.
 *
 * Model class for template
 *
 * LICENSE: This product includes software developed at
 * the Acelle Co., Ltd. (http://acellemail.com/).
 *
 * @category   MVC Model
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

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use Validator;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Acelle\Library\Tool;
use Acelle\Library\Rss;
use Acelle\Library\Storage\Contracts\Storable;
use Illuminate\Validation\ValidationException;

class Template extends Model implements Storable
{
    const STORAGE_DIR = 'templates';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'content',
    ];

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating item.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (Template::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;

            // Update custom order
            Template::getAll()->increment('custom_order', 1);
            $item->custom_order = 0;
        });

        static::deleted(function ($item) {
            $uploaded_dir = $item->getStoragePath();
            \Acelle\Library\Tool::xdelete($uploaded_dir);
        });
    }

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    public function admin()
    {
        return $this->belongsTo('Acelle\Model\Admin');
    }

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAll()
    {
        return self::select('*');
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $query = self::select('templates.*');

        // Keyword
        if (!empty(trim($request->keyword))) {
            $query = $query->where('name', 'like', '%'.$request->keyword.'%');
        }

        // filters
        $filters = $request->filters;

        // Customer
        if (!empty($request->customer_id)) {
            if (!empty($filters) && !empty($filters['from'])) {
                if ($filters['from'] == 'mine') {
                    $query = $query->where('customer_id', '=', $request->customer_id);
                } elseif ($filters['from'] == 'gallery') {
                    $query = $query->where('customer_id', '=', null);
                } else {
                    $query = $query->where('customer_id', '=', null)
                        ->orWhere('customer_id', '=', $request->customer_id);
                }
            }
        }

        // from
        if ($request->from) {
            if ($request->from == 'mine') {
                $query = $query->where('customer_id', '=', $request->customer_id);
            } elseif ($request->from == 'gallery') {
                $query = $query->where('customer_id', '=', null);
            } else {
                $query = $query->where('customer_id', '=', null)
                    ->orWhere('customer_id', '=', $request->customer_id);
            }
        }

        // Admin
        if (!empty($request->admin_id)) {
            $query = $query->where('admin_id', '=', $request->admin_id);
        }

        return $query;
    }

    /**
     * Search items.
     *
     * @return collect
     */
    public static function search($request)
    {
        $query = self::filter($request);

        if (!empty($request->sort_order)) {
            $query = $query->orderBy($request->sort_order, $request->sort_direction);
        }

        return $query;
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function adminFilter($request)
    {
        $user = $request->user();
        $query = self::where('shared', '=', true);

        // Keyword
        if (!empty(trim($request->keyword))) {
            $query = $query->where('name', 'like', '%'.$request->keyword.'%');
        }

        return $query;
    }

    /**
     * Search items.
     *
     * @return collect
     */
    public static function adminSearch($request)
    {
        $query = self::adminFilter($request);

        $query = $query->orderBy($request->sort_order, $request->sort_direction);

        return $query;
    }

    /**
     * Find item by uid.
     *
     * @return object
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /**
     * Define all template styles.
     *
     * @return object
     */
    public static function templateStyles()
    {
        return [
            '1_column' => [
                'full_width_banner',
                'single_text_row',
                'footer',
            ],
            '1_2_column' => [
                'full_width_banner',
                'single_text_row',
                'two_image_text_columns',
                'footer',
            ],
            '1_2_1_column' => [
                'full_width_banner',
                'single_text_row',
                'two_image_text_columns',
                'single_text_row',
                'footer',
            ],
            '1_3_column' => [
                'full_width_banner',
                'single_text_row',
                'three_image_text_columns',
                'single_text_row',
                'footer',
            ],
            '1_3_1_column' => [
                'full_width_banner',
                'single_text_row',
                'three_image_text_columns',
                'single_text_row',
                'footer',
            ],
            '1_3_2_column' => [
                'full_width_banner',
                'single_text_row',
                'three_image_text_columns',
                'two_image_text_columns',
                'footer',
            ],
            '2_column' => [
                'full_width_banner',
                'two_image_text_columns',
                'footer',
            ],
            '2_1_column' => [
                'full_width_banner',
                'two_image_text_columns',
                'single_text_row',
                'footer',
            ],
            '2_1_2_column' => [
                'full_width_banner',
                'two_image_text_columns',
                'single_text_row',
                'two_image_text_columns',
                'footer',
            ],
            '3_1_3_column' => [
                'full_width_banner',
                'three_image_text_columns',
                'single_text_row',
                'three_image_text_columns',
                'footer',
            ],
        ];
    }

    /**
     * Template tags.
     *
     * All availabel template tags
     */
    public static function tags($list = null)
    {
        $tags = [];

        $tags[] = ['name' => 'SUBSCRIBER_EMAIL', 'required' => false];

        // List field tags
        if (isset($list)) {
            foreach ($list->fields as $field) {
                if ($field->tag != 'EMAIL') {
                    $tags[] = ['name' => 'SUBSCRIBER_'.$field->tag, 'required' => false];
                }
            }
        }

        $tags = array_merge($tags, [
            ['name' => 'UNSUBSCRIBE_URL', 'required' => (\Auth::user()->customer && \Auth::user()->customer->getOption('unsubscribe_url_required') == 'yes' ? true : false)],
            ['name' => 'SUBSCRIBER_UID', 'required' => false],
            ['name' => 'WEB_VIEW_URL', 'required' => false],
            ['name' => 'CAMPAIGN_NAME', 'required' => false],
            ['name' => 'CAMPAIGN_UID', 'required' => false],
            ['name' => 'CAMPAIGN_SUBJECT', 'required' => false],
            ['name' => 'CAMPAIGN_FROM_EMAIL', 'required' => false],
            ['name' => 'CAMPAIGN_FROM_NAME', 'required' => false],
            ['name' => 'CAMPAIGN_REPLY_TO', 'required' => false],
            ['name' => 'CURRENT_YEAR', 'required' => false],
            ['name' => 'CURRENT_MONTH', 'required' => false],
            ['name' => 'CURRENT_DAY', 'required' => false],
            ['name' => 'CONTACT_NAME', 'required' => false],
            ['name' => 'CONTACT_COUNTRY', 'required' => false],
            ['name' => 'CONTACT_STATE', 'required' => false],
            ['name' => 'CONTACT_CITY', 'required' => false],
            ['name' => 'CONTACT_ADDRESS_1', 'required' => false],
            ['name' => 'CONTACT_ADDRESS_2', 'required' => false],
            ['name' => 'CONTACT_PHONE', 'required' => false],
            ['name' => 'CONTACT_URL', 'required' => false],
            ['name' => 'CONTACT_EMAIL', 'required' => false],
            ['name' => 'LIST_NAME', 'required' => false],
            ['name' => 'LIST_SUBJECT', 'required' => false],
            ['name' => 'LIST_FROM_NAME', 'required' => false],
            ['name' => 'LIST_FROM_EMAIL', 'required' => false],
        ]);

        return $tags;
    }

    /**
     * Replace url from content.
     */
    public function replaceHtmlUrl($template_path)
    {
        $this->content = \Acelle\Library\Tool::replaceTemplateUrl($this->content, $template_path);
    }

    /**
     * Display creator name.
     *
     * @return string
     */
    public function displayCreatorName()
    {
        return is_object($this->admin) ? $this->admin->displayName() : (is_object($this->customer) ? $this->customer->displayName() : '');
    }

    /**
     * Copy new template.
     */
    public function copy($name, $customer = null, $admin = null)
    {
        $copy = $this->replicate();
        $copy->name = $name;
        $copy->created_at = \Carbon\Carbon::now();
        $copy->updated_at = \Carbon\Carbon::now();
        $copy->custom_order = 0;
        if ($customer) {
            $copy->admin_id = null;
            $copy->customer_id = $customer->id;
        }
        if ($admin) {
            $copy->admin_id = $admin->id;
            $copy->customer_id = null;
        }
        $copy->save();

        // Copy uploaded folder
        if (file_exists($this->getStoragePath())) {
            if (!file_exists($copy->getStoragePath())) {
                mkdir($copy->getStoragePath(), 0777, true);
            }

            \Acelle\Library\Tool::xcopy($this->getStoragePath(), $copy->getStoragePath());
        }

        return $copy;
    }

    /**
     * Get public template upload uri.
     */
    public function getUploadUri()
    {
        return 'app/templates/template_'.$this->uid;
    }

    /**
     * Get public template upload dir.
     */
    public function getUploadDir()
    {
        return storage_path($this->getUploadUri());
    }

    /**
     * Load from directory.
     */
    public function loadFromDirectory($tmp_path)
    {
        // try to find the main file, index.html | index.html | file_name.html | ...
        $main_file = null;
        $thumb = null;
        $sub_path = '';

        // find index
        $possible_main_file_names = array('index.html', 'index.htm');
        foreach ($possible_main_file_names as $name) {
            if (is_file($file = $tmp_path.'/'.$name)) {
                $main_file = $file;
                break;
            }
            $dirs = array_filter(glob($tmp_path.'/'.'*'), 'is_dir');
            foreach ($dirs as $sub) {
                if (is_file($file = $sub.'/'.$name)) {
                    $main_file = $file;
                    $sub_path = explode('/', $sub)[count(explode('/', $sub)) - 1].'/';
                    break;
                }
            }
        }
        // if not find any first html file
        if ($main_file === null) {
            $objects = scandir($tmp_path);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (!is_dir($tmp_path.'/'.$object)) {
                        if (preg_match('/\.html?$/i', $object)) {
                            $main_file = $tmp_path.'/'.$object;
                            break;
                        }
                    }
                }
            }
            $dirs = array_filter(glob($tmp_path.'/'.'*'), 'is_dir');
            foreach ($dirs as $sub) {
                $objects = scandir($sub);
                foreach ($objects as $object) {
                    if ($object != '.' && $object != '..') {
                        if (!is_dir($sub.'/'.$object)) {
                            if (preg_match('/\.html?$/i', $object)) {
                                $main_file = $sub.'/'.$object;
                                $sub_path = explode('/', $sub)[count(explode('/', $sub)) - 1].'/';
                                break;
                            }
                        }
                    }
                }
            }
        }

        // read main file content
        $html_content = trim(file_get_contents($main_file));
        $this->content = $html_content;
        $this->untransform();
        $this->save();

        // upload path
        $upload_path = $this->getStoragePath();

        // copy all folder to upload path
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        // exec("cp -r {$tmp_path}/* {$public_upload_path}/");
        Tool::xcopy($tmp_path, $upload_path);
    }

    /**
     * Upload a template.
     */
    public static function upload($request, $asAdmin = false)
    {
        $user = $request->user();

        $rules = array(
            'file' => 'required|mimetypes:application/zip,application/octet-stream,application/x-zip-compressed,multipart/x-zip',
            'name' => 'required',
        );

        $validator = Validator::make($request->all(), $rules, [
            'file.mimetypes' => 'Input must be a valid .zip file',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // move file to temp place
        $tmp_path = storage_path('tmp/uploaded_template_'.$user->id.'_'.time());
        $file_name = $request->file('file')->getClientOriginalName();
        $request->file('file')->move($tmp_path, $file_name);
        $tmp_zip = join_paths($tmp_path, $file_name);

        // read zip file check if zip archive invalid
        $zip = new ZipArchive();
        if ($zip->open($tmp_zip, ZipArchive::CREATE) !== true) {
            // @todo hack
            // $validator = Validator::make([], []); // Empty data and rules fields
            $validator->errors()->add('file', 'Cannot open .zip file');
            throw new ValidationException($validator);
        }

        // unzip template archive and remove zip file
        $zip->extractTo($tmp_path);
        $zip->close();
        unlink($tmp_zip);

        // Save new template
        $template = new self();
        if ($asAdmin) {
            $template->admin_id = $user->admin->id;
            $template->source = 'upload';
        } else {
            $template->customer_id = $user->customer->id;
        }

        $template->fill($request->all());
        $template->untransform();
        $template->save();

        $template->loadFromDirectory($tmp_path);

        // remove tmp folder
        // exec("rm -r {$tmp_path}");
        Tool::xdelete($tmp_path);

        return $template;
    }

    public function getArchivePath() : string
    {
        return join_paths('users', $this->getPath().'.zip');
    }

    public function toZip() : string
    {
        // Get real path for our folder
        $rootPath = $this->getStoragePath();
        $outputPath = join_paths('/tmp/', $this->uid.'.zip');

        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();

        return $outputPath;
    }

    public function getPath()
    {
        if ($this->customer) {
            $dir_id = $this->customer->user->uid;
        } else {
            $dir_id = 'admin';
        }

        return join_paths($dir_id, self::STORAGE_DIR, $this->uid);
    }

    /**
     * Get public campaign upload dir.
     */
    public function getStoragePath($path = '/')
    {
        if ($this->customer) {
            // storage/app/users/{uid}/templates
            $base = $this->customer->user->getStoragePath(self::STORAGE_DIR);
        } else {
            // storage/app/users/admin/templates
            // IMPORTANT: templates are created from migration without associating with an admin
            $base = $this->getAdminStoragePath(self::STORAGE_DIR);
        }

        $base = join_paths($base, $this->uid);

        if (!\File::exists($base)) {
            \File::makeDirectory($base, 0777, true, true);
        }

        return join_paths($base, $path);
    }

    public function getAdminStoragePath($path = '')
    {
        $base = storage_path('app/users/admin');

        if (!\File::exists($base)) {
            \File::makeDirectory($base, 0777, true, true);
        }

        return join_paths($base, $path);
    }

    /**
     * Get thumb.
     */
    public function getThumbName()
    {
        // find index
        $names = array('thumbnail.png', 'thumbnail.jpg', 'thumbnail.png', 'thumb.jpg', 'thumb.png');
        foreach ($names as $name) {
            if (is_file($file = $this->getStoragePath().$name)) {
                return $name;
            }
        }

        return;
    }

    /**
     * Get thumb.
     */
    public function getThumb()
    {
        // find index
        if ($this->getThumbName()) {
            return $this->getStoragePath().$this->getThumbName();
        }

        return;
    }

    /**
     * Get thumb.
     */
    public function getThumbUrl()
    {
        if ($this->getThumbName()) {
            return route('template_assets', ['uid' => $this->uid, 'path' => $this->getThumbName()]);
        } else {
            return url('assets/images/placeholder.jpg');
        }
    }

    /**
     * transform URL.
     */
    public function untransform()
    {
        // replace absolute urls
        $this->content = str_replace(
            route('template_assets', ['uid' => $this->uid, 'path' => '']).'/',
            '',
            tinyDocTypeUntransform($this->content)
        );

        // remove title tag
        $this->content = strip_tags_only($this->content, 'title');
    }

    /**
     * transform URL.
     */
    public function transform($useTrackingHost = false)
    {
        $this->content = $this->transformWithoutUpdate($this->content, $useTrackingHost);
        return $this->content;
    }

    /**
     * transform URL.
     */
    public function transformWithoutUpdate($html, $useTrackingHost = false)
    {
        // Notice that there are 2 types of assets that need to untransform
        // #1 Campaign relative URL
        // #2 User assets (uploaded by file manager) absolute URL
        // 
        $host = config('app.url'); // Default host
        
        if ($useTrackingHost) {
            // Replace #2
            $html = str_replace($host, $this->getTrackingHost(), $html);
            $host = $this->getTrackingHost();
        }

        // Replace #1
        $path = join_url($host, route('template_assets', ['uid' => $this->uid, 'path' => ''], false));
        $html = \Acelle\Library\Tool::replaceTemplateUrl($html, $path);    

        // By the way, fix <html>
        $html = tinyDocTypeTransform($html);

        return $html;
    }

    /**
     * transform URL.
     */
    public function render()
    {
        $body = $this->transformWithoutUpdate($this->content, $useTrackingHost = false);
        return $body;
    }

    /**
     * Build Email HTML content.
     *
     * @return string
     */
    public function getHtmlContent($subscriber = null, $msgId = null, $server = null)
    {
        // @note: IMPORTANT: the order must be as follows
        // * addTrackingURL
        // * appendOpenTrackingUrl
        // * tagMessage
         // STEP 01. Get RAW content
        $body = $this->content;

        // STEP 02. Append footer
        // Not applied
        
        // STEP 03. Parse RSS
        if (Setting::isYes('rss.enabled')) {
            $body = Rss::parse($body);
        }

        // STEP 04. Replace Bare linefeed
        // Replace bare line feed char which is not accepted by Outlook, Yahoo, AOL...
        // Not applied

        // STEP 05. Transform URLs
        // Note that $useTrackingHost is not needed
        $body = $this->transformWithoutUpdate($body, $useTrackingHost = false);

        if (is_null($msgId)) {
            $msgId = 'SAMPLE'; // for preview mode
        }

        // STEP 08. Transform Tags
        if (!is_null($subscriber)) {
            // Transform tags
            $body = $this->tagMessage($body, $subscriber, $msgId, $server);
        }

        // STEP 09. Make CSS inline
        //
        // Transform CSS/HTML content to inline CSS
        // Be carefule, it will make
        //       <a href="{BUNSUBSCRIBE_URL}" 
        // become
        //       <a href="%7BUNSUBSCRIBE_URL%7D" 
        $body = $this->inlineHtml($body);

        return $body;
    }

    /**
     * Upload asset.
     */
    public function uploadAsset($file)
    {
        if ($this->customer) {
            // upload to user home/files/ folder
            return $this->customer->user->uploadAsset($file);
        } else {
            // upload to template
            return $this->admin->user->uploadAsset($file);
        }
    }

    /**
     * Template tags.
     *
     * All availabel template tags
     */
    public static function builderTags($list = null)
    {
        $tags = self::tags($list);

        $result = [];

        if (true) {
            
            // Unsubscribe link
            $result[] = [
                'type' => 'label',
                'text' => '<a href="{UNSUBSCRIBE_URL}">' . trans('messages.editor.unsubscribe_text') . '</a>',
                'tag' => '{UNSUBSCRIBE_LINK}',
                'required' => true,
            ];
            
            // web view link
            $result[] = [
                'type' => 'label',
                'text' => '<a href="{WEB_VIEW_URL}">' . trans('messages.editor.click_view_web_version') . '</a>',
                'tag' => '{WEB_VIEW_LINK}',
                'required' => true,
            ];
        }

        foreach ($tags as $tag) {
            $result[] = [
                'type' => 'label',
                'text' => '{'.$tag['name'].'}',
                'tag' => '{'.$tag['name'].'}',
                'required' => true,
            ];
        }

        return $result;
    }

    /**
     * Create template from layout.
     *
     * All availabel template tags
     */
    public function addTemplateFromLayout($layout)
    {
        $sDir = database_path('layouts/'.$layout);
        $this->loadFromDirectory($sDir);
    }

    /**
     * Load templates from folder.
     *
     * @return mixed
     */
    public function load($path)
    {
        // copy file and get thumb
        \Acelle\Library\Tool::xcopy($path, $this->getUploadDir());
        $this->image = $this->getUploadDir().'/thumb.png';
        $this->replaceHtmlUrl('/'.$this->getUploadUri().'/');
        $this->untransform();
        $this->save();
    }

    /**
     * Load demo templates from storage/themes.
     *
     * @return mixed
     */
    public static function loadFromThemes()
    {
        $themePath = database_path('themes');
        $folders = scandir($themePath);

        foreach ($folders as $folder) {
            $fullPath = $themePath.'/'.$folder;

            if ($folder != '.' && $folder != '..' && is_dir($fullPath)) {
                $files = glob($fullPath . "/index.html");
                $content = file_get_contents($files[0]);
                preg_match_all('/(<title\>([^<]*)\<\/title\>)/i', $content, $m);
                $title = $m[2][0];

                // Create admin template
                $template = new self();
                $template->name = $title;
                $template->admin_id = 1;

                $template->loadFromDirectory($fullPath);
            }
        }
    }

    /**
     * Get builder templates.
     *
     * @return mixed
     */
    public function getBuilderTemplates($customer)
    {
        $result = [];

        // Gallery
        $templates = self::where('customer_id', '=', null)
            ->orWhere('customer_id', '=', $customer->id)
            ->orderBy('customer_id')
            ->get();

        foreach ($templates as $template) {
            $result[] = [
                'name' => $template->name,
                'url' => action('TemplateController@builderChangeTemplate', ['uid' => $this->uid, 'change_uid' => $template->uid]),
                'thumbnail' => $template->getThumbUrl(),
            ];
        }

        return $result;
    }

    /**
     * Get builder templates.
     *
     * @return mixed
     */
    public function getBuilderAdminTemplates()
    {
        $result = [];

        // Gallery
        $templates = self::where('customer_id', '=', null)
            ->get();

        foreach ($templates as $template) {
            $result[] = [
                'name' => $template->name,
                'url' => action('Admin\TemplateController@builderChangeTemplate', ['uid' => $this->uid, 'change_uid' => $template->uid]),
                'thumbnail' => $template->getThumbUrl(),
            ];
        }

        return $result;
    }

    /**
     * Get builder templates.
     *
     * @return mixed
     */
    public function changeTemplate($template)
    {
        \Acelle\Library\Tool::xcopy($template->getUploadDir(), $this->getUploadDir());
        $this->content = $template->content;
        $this->image = $template->image;
        // replace image url
        $this->content = str_replace($template->getUploadUri(), $this->getUploadUri(), $this->content);
        $this->untransform();
        $this->save();
    }

    /**
     * Upload template thumbnail.
     *
     * @return mixed
     */
    public function uploadThumbnail($file)
    {
        $file->move($this->getStoragePath(), 'thumbnail.png');
    }

    /**
     * Upload template thumbnail Url.
     *
     * @return mixed
     */
    public function uploadThumbnailUrl($url)
    {
        $contents = file_get_contents($url);
        file_put_contents($this->getStoragePath().'thumbnail.png', $contents);
    }

    public function inlineHtml($html)
    {
        return $html;
    }
}
