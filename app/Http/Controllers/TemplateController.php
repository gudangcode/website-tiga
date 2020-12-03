<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Acelle\Model\Template;
use Acelle\Library\Rss;
use Acelle\Model\Setting;
use App;

class TemplateController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->merge(array("customer_id" => $request->user()->customer->id));
        $templates = Template::search($request);

        return view('templates.index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing(Request $request)
    {
        $request->merge(array("customer_id" => $request->user()->customer->id));
        $templates = Template::search($request)->paginate($request->per_page);

        return view('templates._list', [
            'templates' => $templates,
        ]);
    }

    /**
     * Display a listing of the resource for choose one.
     *
     * @return \Illuminate\Http\Response
     */
    public function choosing(Request $request)
    {
        $request->merge(array("customer_id" => $request->user()->customer->id));
        $templates = Template::search($request)->paginate($request->per_page);
        $campaign = \Acelle\Model\Campaign::findByUid($request->campaign_uid);

        return view('templates._list_choose', [
            'templates' => $templates,
            'campaign' => $campaign,
        ]);
    }

    /**
     * Content of template.
     *
     * @return \Illuminate\Http\Response
     */
    public function content(Request $request)
    {
        $template = Template::findByUid($request->uid);

        // authorize
        if (!$request->user()->customer->can('view', $template)) {
            return $this->notAuthorized();
        }

        echo $template->content;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // Generate info
        $user = $request->user();
        $template = new Template();

        // authorize
        if (!$request->user()->customer->can('create', Template::class)) {
            return $this->notAuthorized();
        }

        // Get old post values
        if (null !== $request->old()) {
            $template->fill($request->old());
        }

        return view('templates.create', [
            'template' => $template,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Generate info
        $user = $request->user();
        $customer = $request->user()->customer;

        $template = new Template();
        $template->customer_id = $customer->id;

        // authorize
        if (!$request->user()->customer->can('update', $template)) {
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            $rules = array(
                'name' => 'required',
                'content' => 'required',
            );

            $this->validate($request, $rules);

            // Save template
            $template->fill($request->all());
            $template->source = 'editor';
            if (isset($request->source)) {
                $template->source = $request->source;
            }

            //// update content
            //$template->content = preg_replace('/href\=\'([^\']*\{)/',"href='{", $template->content);
            //$template->content = preg_replace('/href\=\"([^\"]*\{)/','href="{', $template->content);
            $template->untransform();
            $template->save();

            if (!empty(Setting::get('storage.s3'))) {
                App::make('xstore')->store($template);
            }

            // Redirect to my lists page
            $request->session()->flash('alert-success', trans('messages.template.created'));

            return redirect()->action('TemplateController@index');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $uid)
    {
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($uid);

        // authorize
        if (!$request->user()->customer->can('update', $template)) {
            return $this->notAuthorized();
        }

        // Get old post values
        if (null !== $request->old()) {
            $template->fill($request->old());
        }

        return view('templates.edit', [
            'template' => $template,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($request->uid);

        // authorize
        if (!$request->user()->customer->can('update', $template)) {
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('patch')) {
            // Save template
            $template->fill($request->all());

            $rules = array(
                'name' => 'required',
                'content' => 'required',
            );

            // make validator
            $validator = \Validator::make($request->all(), $rules);
            
            // redirect if fails
            if ($validator->fails()) {
                // faled
                return response()->json($validator->errors(), 400);
            }
            
            $template->untransform();
            $template->save();

            // success
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.template.updated'),
            ], 201);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }

    /**
     * Custom sort items.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function sort(Request $request)
    {
        $sort = json_decode($request->sort);
        foreach ($sort as $row) {
            $item = Template::findByUid($row[0]);

            // authorize
            if (!$request->user()->customer->can('update', $item)) {
                return $this->notAuthorized();
            }

            $item->custom_order = $row[1];
            $item->untransform();
            $item->save();
        }

        echo trans('messages.templates.custom_order.updated');
    }

    /**
     * Upload template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        // authorize
        if (!$request->user()->customer->can('create', Template::class)) {
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            $template = Template::upload($request);

            if (!empty(Setting::get('storage.s3'))) {
                App::make('xstore')->store($template);
            }

            $request->session()->flash('alert-success', trans('messages.template.uploaded'));
            return redirect()->action('TemplateController@index');
        }

        return view('templates.upload');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        if (isSiteDemo()) {
            return response()->json([
                'status' => 'notice',
                'message' => trans('messages.operation_not_allowed_in_demo'),
            ]);
        }

        $items = Template::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            // authorize
            if ($request->user()->customer->can('delete', $item)) {
                $item->delete();
            }
        }

        // Redirect to my lists page
        echo trans('messages.templates.deleted');
    }

    /**
     * Preview template.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function preview(Request $request, $id)
    {
        $template = Template::findByUid($id);

        // authorize
        if (!$request->user()->customer->can('preview', $template)) {
            return $this->not_authorized();
        }

        return view('templates.preview', [
            'template' => $template,
        ]);
    }

    /**
     * Save template screenshot.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function saveImage(Request $request, $id)
    {
        $template = Template::findByUid($id);

        // authorize
        if (!$request->user()->customer->can('saveImage', $template)) {
            return $this->not_authorized();
        }
        
        // if thumb is default
        if (substr($template->image, 0, 1) == '/') {
            return;
        }

        $upload_loca = 'app/email_templates/';
        $upload_path = storage_path($upload_loca);
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        $filename = 'screenshot-'.$id.'.png';

        // remove "data:image/png;base64,"
        $uri = substr($request->data, strpos($request->data, ',') + 1);

        // save to file
        file_put_contents($upload_path.$filename, base64_decode($uri));

        // create thumbnails
        $img = \Image::make($upload_path.$filename);
        $img->fit(178, 200)->save($upload_path.$filename.'.thumb.jpg');

        // save
        $template->image = $upload_loca.$filename;
        $template->untransform();
        $template->save();
    }

    /**
     * Buiding email template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function build(Request $request)
    {
        $template = new Template();
        $template->name = trans('messages.untitled_template');

        // authorize
        if (!$request->user()->customer->can('create', Template::class)) {
            return $this->notAuthorized();
        }

        $elements = [];
        if (isset($request->style)) {
            $elements = Template::templateStyles()[$request->style];
        }

        return view('templates.build', [
            'template' => $template,
            'elements' => $elements
        ]);
    }

    /**
     * Buiding email template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function rebuild(Request $request)
    {
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($request->uid);

        // authorize
        if (!$request->user()->customer->can('update', $template)) {
            return $this->notAuthorized();
        }

        return view('templates.rebuild', [
            'template' => $template,
        ]);
    }

    /**
     * Select template style.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function buildSelect(Request $request)
    {
        $template = new Template();

        return view('templates.build_start', [
            'template' => $template,
        ]);
    }

    /**
     * Copy template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function copy(Request $request)
    {
        $template = Template::findByUid($request->uid);

        if ($request->isMethod('post')) {
            // authorize
            if (!$request->user()->customer->can('copy', $template)) {
                return $this->notAuthorized();
            }

            $template->copy($request->name, $request->user()->customer);

            echo trans('messages.template.copied');
            return;
        }

        return view('templates.copy', [
            'template' => $template,
        ]);
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function builderEdit(Request $request, $uid)
    {
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($uid);

        // authorize
        if (!$request->user()->customer->can('update', $template)) {
            return $this->notAuthorized();
        }
        
        // validate and save posted data
        if ($request->isMethod('post')) {
            $rules = array(
                'content' => 'required',
            );

            $this->validate($request, $rules);
            
            $template->content = $request->content;
            $template->untransform();
            $template->save();
            
            return response()->json([
                'status' => 'success',
            ]);
        }

        return view('templates.builder.edit', [
            'template' => $template,
            'templates' => $template->getBuilderTemplates($user->customer),
        ]);
    }
    
    /**
     * Change template from exist template.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function builderChangeTemplate(Request $request, $uid, $change_uid)
    {
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($uid);
        $changeTemplate = Template::findByUid($change_uid);

        // authorize
        if (!$request->user()->customer->can('update', $template)) {
            return $this->notAuthorized();
        }
        
        $template->changeTemplate($changeTemplate);
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function builderEditContent(Request $request, $uid)
    {
        // Generate info
        $user = $request->user();
        $template = Template::findByUid($uid);
        
        // authorize
        if (!$request->user()->customer->can('update', $template)) {
            return $this->notAuthorized();
        }

        return view('templates.builder.content', [
            'content' => $template->render(),
        ]);
    }
    
    /**
     * Upload asset to builder.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function builderAsset(Request $request, $uid)
    {
        $template = Template::findByUid($uid);
        
        // authorize
        if (!$request->user()->customer->can('update', $template)) {
            return $this->notAuthorized();
        }
        
        $filename = $template->uploadAsset($request->file('file'));

        return response()->json([
            'url' => route('customer_files', ['uid' => $request->user()->uid, 'name' => $filename])
        ]);
    }
    
    /**
     * Create template / temlate selection.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function builderCreate(Request $request)
    {
        $customer = $request->user()->customer;
        
        $template = new Template();
        $template->name = trans('messages.untitled_template');
        
        // authorize
        if (!$request->user()->customer->can('create', Template::class)) {
            return $this->notAuthorized();
        }
        
        // Gallery
        $templates = Template::where('customer_id', '=', null);
        
        // validate and save posted data
        if ($request->isMethod('post')) {
            if ($request->layout) {
                $template = new Template();
                $template->customer_id = $customer->id;
                $template->name = $request->name;
                $template->untransform();
                $template->save();

                $template->addTemplateFromLayout($request->layout);
            }
            
            if ($request->template) {
                $currentTemplate = Template::findByUid($request->template);
                
                $template = $currentTemplate->copy($request->name, $customer);
            }
            
            return redirect()->action('TemplateController@index');
        }

        return view('templates.builder.create', [
            'template' => $template,
            'templates' => $templates,
        ]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function builderTemplates(Request $request)
    {
        $request->merge(array("customer_id" => $request->user()->customer->id));
        $templates = Template::search($request)->paginate($request->per_page);
        
        // authorize
        if (!$request->user()->customer->can('create', Template::class)) {
            return $this->notAuthorized();
        }

        return view('templates.builder.templates', [
            'templates' => $templates,
        ]);
    }
    
    /**
     * Update template thumb.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateThumb(Request $request, $uid)
    {
        $template = Template::findByUid($uid);
        
        // authorize
        if (!$request->user()->customer->can('update', $template)) {
            return $this->notAuthorized();
        }

        if ($request->isMethod('post')) {
            // make validator
            $validator = \Validator::make($request->all(), [
                'file' => 'required',
            ]);

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('templates.updateThumb', [
                    'template' => $template,
                    'errors' => $validator->errors(),
                ], 400);
            }
            
            // update thumb
            $template->uploadThumbnail($request->file);

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.template.thumb.uploaded'),
            ], 201);
        }

        return view('templates.updateThumb', [
            'template' => $template,
        ]);
    }

    /**
     * Update template thumb url.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateThumbUrl(Request $request, $uid)
    {
        $template = Template::findByUid($uid);
        
        // authorize
        if (!$request->user()->customer->can('update', $template)) {
            return $this->notAuthorized();
        }

        if ($request->isMethod('post')) {
            // make validator
            $validator = \Validator::make($request->all(), [
                'url' => 'required|url',
            ]);

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('templates.updateThumbUrl', [
                    'template' => $template,
                    'errors' => $validator->errors(),
                ], 400);
            }
            
            // update thumb
            $template->uploadThumbnailUrl($request->url);

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.template.thumb.uploaded'),
            ], 201);
        }

        return view('templates.updateThumbUrl', [
            'template' => $template,
        ]);
    }
}
