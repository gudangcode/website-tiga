<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use Acelle\Http\Requests;
use Acelle\Model\Automation2;
use Acelle\Model\MailList;
use Acelle\Model\Email;
use Acelle\Model\Attachment;
use Acelle\Model\Template;
use Acelle\Model\Subscriber;
use Illuminate\Support\Facades\Storage;

class Automation2Controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $automations = $user->customer->automation2s();

        return view('automation2.index', [
            'automations' => $automations,
        ]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing(Request $request)
    {
        $automations = Automation2::search($request)->paginate($request->per_page);

        return view('automation2._list', [
            'automations' => $automations,
        ]);
    }
    
    /**
     * Creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $customer = $request->user()->customer;
        
        // init automation
        $automation = new Automation2([
            'name' => trans('messages.automation.untitled'),
        ]);
        $automation->status = Automation2::STATUS_INACTIVE;
        
        // authorize
        if (\Gate::denies('create', $automation)) {
            return $this->noMoreItem();
        }
        
        // saving
        if ($request->isMethod('post')) {
            // fill before save
            $automation->fillRequest($request);
            
            // make validator
            $validator = Validator::make($request->all(), $automation->rules());
            
            // redirect if fails
            if ($validator->fails()) {
                return response()->view('automation2.create', [
                    'automation' => $automation,
                    'errors' => $validator->errors(),
                ], 400);
            }
            
            // pass validation and save
            $automation->mail_list_id = MailList::findByUid($request->mail_list_uid)->id;
            $automation->customer_id = $customer->id;
            $automation->data = '[{"title":"Click to choose a trigger","id":"trigger","type":"ElementTrigger","options":{"init":"false", "key": ""}}]';
            $automation->save();
            
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.created.redirecting'),
                'url' => action('Automation2Controller@edit', ['uid' => $automation->uid])
            ], 201);
        }
        
        return view('automation2.create', [
            'automation' => $automation,
        ]);
    }
    
    /**
     * Update automation.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $uid)
    {
        $customer = $request->user()->customer;
        
        // find automation
        $automation = Automation2::findByUid($uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        // fill before save
        $automation->fillRequest($request);
            
        // make validator
        $validator = Validator::make($request->all(), $automation->rules());
            
        // redirect if fails
        if ($validator->fails()) {
            return response()->view('automation2.settings', [
                'automation' => $automation,
                'errors' => $validator->errors(),
            ], 400);
        }
            
        // pass validation and save
        $automation->updateMailList(MailList::findByUid($request->mail_list_uid));
        
        // save
        $automation->save();
            
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.updated'),
        ], 201);
    }
    
    /**
     * Update automation.
     *
     * @return \Illuminate\Http\Response
     */
    public function saveData(Request $request, $uid)
    {
        // find automation
        $automation = Automation2::findByUid($uid);

        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        $automation->saveData($request->data);
    }
    
    /**
     * Creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $automation->updateCacheInBackground();
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        return view('automation2.edit', [
            'automation' => $automation,
        ]);
    }
    
    /**
     * Automation settings in sidebar.
     *
     * @return \Illuminate\Http\Response
     */
    public function settings(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        return view('automation2.settings', [
            'automation' => $automation,
        ]);
    }
    
    /**
     * Select trigger type popup.
     *
     * @return \Illuminate\Http\Response
     */
    public function triggerSelectPupop(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        $types = [
            'welcome-new-subscriber',
            'say-happy-birthday',
            'subscriber-added-date',
            'specific-date',
            'say-goodbye-subscriber',
            'api-3-0',
            'weekly-recurring',
            'monthly-recurring',
        ];
        
        return view('automation2.triggerSelectPupop', [
            'types' => $types,
            'automation' => $automation,
            'trigger' => $automation->getTrigger(),
        ]);
    }
    
    /**
     * Select trigger type confirm.
     *
     * @return \Illuminate\Http\Response
     */
    public function triggerSelectConfirm(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $rules = $this->triggerRules()[$request->key];
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        return view('automation2.triggerSelectConfirm', [
            'key' => $request->key,
            'automation' => $automation,
            'trigger' => $automation->getTrigger(),
            'rules' => $rules,
        ]);
    }

    /**
     * Select trigger type.
     *
     * @return array
     */
    public function triggerRules()
    {
        return [
            'welcome-new-subscriber' => [],
            'say-happy-birthday' => [
                'options.before' => 'required',
                'options.at' => 'required',
                'options.field' => 'required',
            ],
            'specific-date' => [
                'options.date' => 'required',
                'options.at' => 'required',
            ],
            'say-goodbye-subscriber' => [],
            'api-3-0' => [],
            'subscriber-added-date' => [
                'options.delay' => 'required',
                'options.at' => 'required',
            ],
            'weekly-recurring' => [
                'options.days_of_week' => 'required',
                'options.at' => 'required',
            ],
            'monthly-recurring' => [
                'options.days_of_month' => 'required|array|min:1',
                'options.at' => 'required',
            ],
        ];
    }
    
    /**
     * Select trigger type.
     *
     * @return \Illuminate\Http\Response
     */
    public function triggerSelect(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $rules = $this->triggerRules()[$request->options['key']];
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        // make validator
        $validator = Validator::make($request->all(), $rules);
            
        // redirect if fails
        if ($validator->fails()) {
            return response()->view('automation2.triggerSelectConfirm', [
                'key' => $request->options['key'],
                'automation' => $automation,
                'trigger' => $automation->getTrigger(),
                'rules' => $rules,
                'errors' => $validator->errors(),
            ], 400);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.trigger.added'),
            'title' => trans('messages.automation.trigger.title', [
                'title' => trans('messages.automation.trigger.tree.' . $request->options["key"])
            ]),
            'options' => $request->options,
            'rules' => $rules,
        ]);
    }
    
    /**
     * Select action type popup.
     *
     * @return \Illuminate\Http\Response
     */
    public function actionSelectPupop(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        $types = [
            'send-an-email',
            'wait',
            'condition',
        ];
        
        return view('automation2.actionSelectPupop', [
            'types' => $types,
            'automation' => $automation,
            'hasChildren' => $request->hasChildren,
        ]);
    }
    
    /**
     * Select action type confirm.
     *
     * @return \Illuminate\Http\Response
     */
    public function actionSelectConfirm(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        return view('automation2.actionSelectConfirm', [
            'key' => $request->key,
            'automation' => $automation,
            'element' => $automation->getElement(),
        ]);
    }
    
    /**
     * Select trigger type.
     *
     * @return \Illuminate\Http\Response
     */
    public function actionSelect(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        if ($request->key == 'wait') {
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.action.added'),
                'title' => trans('messages.automation.wait.delay.' . $request->time),
                'options' => [
                    'key' => $request->key,
                    'time' => $request->time,
                ],
            ]);
        } elseif ($request->key == 'condition') {
            if ($request->type == 'open') {
                return response()->json([
                    'status' => 'success',
                    'message' => trans('messages.automation.action.added'),
                    'title' => trans('messages.automation.action.condition.read_email.title'),
                    'options' => [
                        'key' => $request->key,
                        'type' => $request->type,
                        'email' => empty($request->email) ? null : $request->email,
                    ],
                ]);
            } elseif ($request->type == 'click') {
                return response()->json([
                    'status' => 'success',
                    'message' => trans('messages.automation.action.added'),
                    'title' => trans('messages.automation.action.condition.click_link.title'),
                    'options' => [
                        'key' => $request->key,
                        'type' => $request->type,
                        'email_link' => empty($request->email_link) ? null : $request->email_link,
                    ],
                ]);
            }
        } else {
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.action.added'),
                'title' => trans('messages.automation.action.title', [
                    'title' => trans('messages.automation.action.' . $request->key)
                ]),
                'options' => [
                    'key' => $request->key,
                    'after' => $request->after,
                ],
            ]);
        }
    }
    
    /**
     * Edit trigger.
     *
     * @return \Illuminate\Http\Response
     */
    public function triggerEdit(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $rules = $this->triggerRules()[$request->key];
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        if ($request->isMethod('post')) {
            // make validator
            $validator = Validator::make($request->all(), $this->triggerRules()[$request->options['key']]);
            $rules = $this->triggerRules()[$request->options['key']];
                
            // redirect if fails
            if ($validator->fails()) {
                return response()->view('automation2.triggerEdit', [
                    'key' => $request->options['key'],
                    'automation' => $automation,
                    'trigger' => $automation->getTrigger(),
                    'rules' => $rules,
                    'errors' => $validator->errors(),
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.trigger.updated'),
                'title' => trans('messages.automation.trigger.title', [
                    'title' => trans('messages.automation.trigger.tree.' . $request->options["key"])
                ]),
                'options' => $request->options,
            ]);
        }
        
        return view('automation2.triggerEdit', [
            'key' => $request->key,
            'automation' => $automation,
            'trigger' => $automation->getTrigger(),
            'rules' => $rules,
        ]);
    }
    
    /**
     * Edit action.
     *
     * @return \Illuminate\Http\Response
     */
    public function actionEdit(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        // saving
        if ($request->isMethod('post')) {
            if ($request->key == 'wait') {
                return response()->json([
                    'status' => 'success',
                    'message' => trans('messages.automation.action.updated'),
                    'title' => trans('messages.automation.wait.delay.' . $request->time),
                    'options' => [
                        'key' => $request->key,
                        'time' => $request->time,
                    ],
                ]);
            } elseif ($request->key == 'condition') {
                if ($request->type == 'open') {
                    return response()->json([
                        'status' => 'success',
                        'message' => trans('messages.automation.action.updated'),
                        'title' => trans('messages.automation.action.condition.read_email.title'),
                        'options' => [
                            'key' => $request->key,
                            'type' => $request->type,
                            'email' => empty($request->email) ? null : $request->email,
                        ],
                    ]);
                } elseif ($request->type == 'click') {
                    return response()->json([
                        'status' => 'success',
                        'message' => trans('messages.automation.action.updated'),
                        'title' => trans('messages.automation.action.condition.click_link.title'),
                        'options' => [
                            'key' => $request->key,
                            'type' => $request->type,
                            'email_link' => empty($request->email_link) ? null : $request->email_link,
                        ],
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => trans('messages.automation.action.updated'),
                    'title' => trans('messages.automation.action.title', [
                        'title' => trans('messages.automation.action.' . $request->key)
                    ]),
                    'options' => [
                        'key' => $request->key,
                        'after' => $request->after,
                    ],
                ]);
            }
        }
        
        return view('automation2.actionEdit', [
            'key' => $request->key,
            'automation' => $automation,
            'element' => $automation->getElement($request->id),
        ]);
    }
    
    /**
     * Email setup.
     *
     * @return \Illuminate\Http\Response
     */
    public function emailSetup(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        
        if ($request->email_uid) {
            $email = Email::findByUid($request->email_uid);
        } else {
            $email = new Email([
                'sign_dkim' => true,
                'track_open' => true,
                'track_click' => true,
                'action_id' => $request->action_id,
            ]);
        }
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        // saving
        if ($request->isMethod('post')) {
            // fill before save
            $email->fillAttributes($request->all());

            // Tacking domain
            if (isset($params['custom_tracking_domain']) && $params['custom_tracking_domain'] && isset($params['tracking_domain_uid'])) {
                $tracking_domain = \Acelle\Model\TrackingDomain::findByUid($params['tracking_domain_uid']);
                if (is_object($tracking_domain)) {
                    $this->tracking_domain_id = $tracking_domain->id;
                } else {
                    $this->tracking_domain_id = null;
                }
            } else {
                $this->tracking_domain_id = null;
            }
            
            // make validator
            $validator = Validator::make($request->all(), $email->rules($request));
            
            // redirect if fails
            if ($validator->fails()) {
                return response()->view('automation2.email.setup', [
                    'automation' => $automation,
                    'email' => $email,
                    'errors' => $validator->errors(),
                ], 400);
            }
            
            // pass validation and save
            $email->automation2_id = $automation->id;
            $email->save();
            
            return response()->json([
                'status' => 'success',
                'title' => trans('messages.automation.send_a_email', ['title' => $email->subject]),
                'message' => trans('messages.automation.email.set_up.success'),
                'url' => action('Automation2Controller@emailTemplate', [
                    'uid' => $automation->uid,
                    'email_uid' => $email->uid,
                ]),
                'options' => [
                    'email_uid' => $email->uid,
                ],
            ], 201);
        }
        
        return view('automation2.email.setup', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Delete automation email.
     *
     * @return \Illuminate\Http\Response
     */
    public function emailDelete(Request $request, $uid, $email_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($email_uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        // delete email
        $email->delete();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.email.deteled'),
        ], 201);
    }
    
    /**
     * Email template.
     *
     * @return \Illuminate\Http\Response
     */
    public function emailTemplate(Request $request, $uid, $email_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($email_uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        if (!$email->hasTemplate()) {
            return redirect()->action('Automation2Controller@templateCreate', [
                'uid' => $automation->uid,
                'email_uid' => $email->uid,
            ]);
        }
        
        return view('automation2.email.template', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }
    
    /**
     * Email show.
     *
     * @return \Illuminate\Http\Response
     */
    public function email(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        return view('automation2.email.index', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }
    
    /**
     * Email confirm.
     *
     * @return \Illuminate\Http\Response
     */
    public function emailConfirm(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        // saving
        if ($request->isMethod('post')) {
        }
        
        return view('automation2.email.confirm', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }
    
    /**
     * Create template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateCreate(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        return view('automation2.email.template.create', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Create template from layout.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateLayout(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        // add layout to campaign template
        if ($request->isMethod('post')) {
            if ($request->layout) {
                $email->addTemplateFromLayout($request->layout);

                // update email plain text
                $email->updatePlainFromContent();

                // update links
                $email->updateLinks();
            }
            
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.email.template.layout.selected'),
                'url' => action('Automation2Controller@templateBuilderSelect', [
                    'uid' => $automation->uid,
                    'email_uid' => $email->uid,
                ]),
            ], 201);
        }

        return view('automation2.email.template.layout', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Select builder for editing template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateBuilderSelect(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        return view('automation2.email.template.templateBuilderSelect', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Edit campaign template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateEdit(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        // save campaign html
        if ($request->isMethod('post')) {
            $rules = array(
                'content' => 'required',
            );
            
            $this->validate($request, $rules);
            
            $email->content = $request->content;
            $email->untransform();
            $email->save();

            // update email plain text
            $email->updatePlainFromContent();

            // update links
            $email->updateLinks();
            
            return response()->json([
                'status' => 'success',
            ]);
        }

        return view('automation2.email.template.edit', [
            'automation' => $automation,
            'list' => $automation->mailList,
            'email' => $email,
            'templates' => $email->getBuilderTemplates($request->user()->customer),
        ]);
    }

    /**
     * Upload asset to builder.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function templateAsset(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        $filename = $email->uploadAsset($request->file('file'));

        return response()->json([
            'url' => route('customer_files', ['uid' => $request->user()->uid, 'name' => $filename])
        ]);
    }

    /**
     * Campaign html content.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateContent(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        return view('automation2.email.template.content', [
            'content' => $email->render(),
        ]);
    }

    /**
     * Create template from theme.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateTheme(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);

        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        if ($request->isMethod('post')) {
            $template = Template::findByUid($request->template_uid);
            $email->copyFromTemplate($template);

            // update email plain text
            $email->updatePlainFromContent();

            // update links
            $email->updateLinks();

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.email.template.theme.selected'),
            ], 201);
        }

        return view('automation2.email.template.theme', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function templateThemeList(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        $request->merge(array("customer_id" => $request->user()->customer->id));
        list($templates, $pagination) = pagination($request, Template::search($request));
        
        return view('automation2.email.template.themeList', [
            'automation' => $automation,
            'email' => $email,
            'templates' => $templates,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Upload template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateUpload(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            list($result, $validator) = $email->uploadTemplate($request);
            
            if (!$result) {
                // update email plain text
                $email->updatePlainFromContent();

                return response()->view('automation2.email.template.upload', [
                    'automation' => $automation,
                    'email' => $email,
                    'errors' => $validator->errors(),
                ], 400);
            } else {
                // update links
                $email->updateLinks();

                return response()->json([
                    'status' => 'success',
                    'message' => trans('messages.automation.email.template.uploaded'),
                ], 201);
            }
        }

        return view('automation2.email.template.upload', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }
    
    /**
     * Remove exist template.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateRemove(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        $email->removeTemplate();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.email.template.removed'),
        ], 201);
    }

    /**
     * Template preview.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templatePreview(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        return view('automation2.email.template.preview', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }
    
    /**
     * Attachment upload.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function emailAttachmentUpload(Request $request, $uid, $email_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        foreach ($request->file as $file) {
            $email->uploadAttachment($file);
        }
    }
    
    /**
     * Attachment remove.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function emailAttachmentRemove(Request $request, $uid, $email_uid, $attachment_uid)
    {
        $automation = Automation2::findByUid($uid);
        $attachment = Attachment::findByUid($request->attachment_uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }
        
        $attachment->remove();
        
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.email.attachment.removed'),
        ], 201);
    }
    
    /**
     * Attachment download.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function emailAttachmentDownload(Request $request, $uid, $email_uid, $attachment_uid)
    {
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($request->email_uid);
        $attachment = Attachment::findByUid($request->attachment_uid);
        
        // authorize
        if (\Gate::denies('read', $automation)) {
            return $this->notAuthorized();
        }
        
        return response()->download(storage_path('app/' . $attachment->file), $attachment->name);
    }
    
    /**
     * Enable automation.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function enable(Request $request)
    {
        $automations = Automation2::whereIn('uid', explode(',', $request->uids));

        foreach ($automations->get() as $automation) {
            // authorize
            if (\Gate::allows('enable', $automation)) {
                $automation->enable();
            }
        }
        
        return response()->json([
            'status' => 'success',
            'message' => trans_choice('messages.automation.enabled', $automations->count()),
        ]);
    }
    
    /**
     * Disable event.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function disable(Request $request)
    {
        $automations = Automation2::whereIn('uid', explode(',', $request->uids));

        foreach ($automations->get() as $automation) {
            // authorize
            if (\Gate::allows('disable', $automation)) {
                $automation->disable();
            }
        }
        
        return response()->json([
            'status' => 'success',
            'message' => trans_choice('messages.automation.disabled', $automations->count()),
        ]);
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
        
        $automations = Automation2::whereIn('uid', explode(',', $request->uids));

        foreach ($automations->get() as $automation) {
            // authorize
            if (\Gate::allows('delete', $automation)) {
                $automation->delete();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => trans_choice('messages.automation.deleted', $automations->count()),
        ]);
    }
    
    /**
     * Automation insight page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function insight(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);
        
        // authorize
        if (\Gate::denies('view', $automation)) {
            return $this->notAuthorized();
        }

        return view('automation2.insight', [
            'automation' => $automation,
            'stats' => $automation->readCache('SummaryStats'),
            'insight' => $automation->getInsight(),
        ]);
    }
    
    /**
     * Automation contacts list.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function contacts(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);
        
        // authorize
        if (\Gate::denies('view', $automation)) {
            return $this->notAuthorized();
        }

        // all or action contacts
        if ($request->action_id) {
            $subscribers = Subscriber::gSearch($automation->subscribers($request->action_id), $request);
        } else {
            $subscribers = Subscriber::gSearch($automation->subscribers(), $request);
        }

        return view('automation2.contacts.index', [
            'automation' => $automation,
            'subscribers' => $subscribers,
        ]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function contactsList(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);
        
        // authorize
        if (\Gate::denies('view', $automation)) {
            return $this->notAuthorized();
        }

        // all or action contacts
        if ($request->action_id) {
            $subscribers = Subscriber::gSearch($automation->subscribers($request->action_id), $request);
        } else {
            $subscribers = Subscriber::gSearch($automation->subscribers(), $request);
        }
        
        list($contacts, $pagination) = pagination($request, $subscribers);
        
        return view('automation2.contacts.list', [
            'automation' => $automation,
            'contacts' => $contacts,
            'pagination' => $pagination,
        ]);
    }
    
    /**
     * Automation timeline.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function timeline(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);
        
        // authorize
        if (\Gate::denies('view', $automation)) {
            return $this->notAuthorized();
        }

        return view('automation2.timeline.index', [
            'automation' => $automation,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function timelineList(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);
        
        // authorize
        if (\Gate::denies('view', $automation)) {
            return $this->notAuthorized();
        }
        
        list($timelines, $pagination) = pagination($request, $automation->timelines());
        
        return view('automation2.timeline.list', [
            'automation' => $automation,
            'timelines' => $timelines,
            'pagination' => $pagination,
        ]);
    }
    
    /**
     * Automation contact profile.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function profile(Request $request, $uid, $contact_uid)
    {
        $automation = Automation2::findByUid($uid);
        $contact = Subscriber::findByUid($contact_uid);
        
        // authorize
        if (\Gate::denies('view', $automation)) {
            return $this->notAuthorized();
        }

        return view('automation2.profile', [
            'automation' => $automation,
            'contact' => $contact,
        ]);
    }

    /**
     * Automation remove contact.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function removeContact(Request $request, $uid, $contact_uid)
    {
        $automation = Automation2::findByUid($uid);
        $contact = Subscriber::findByUid($contact_uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.contact.deleted'),
        ], 201);
    }

    /**
     * Automation tag contact.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function tagContact(Request $request, $uid, $contact_uid)
    {
        $automation = Automation2::findByUid($uid);
        $contact = Subscriber::findByUid($contact_uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        // saving
        if ($request->isMethod('post')) {
            $contact->updateTags($request->tags);

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.contact.tagged', [
                    'contact' => $contact->getFullName(),
                ]),
            ], 201);
        }

        return view('automation2.contacts.tagContact', [
            'automation' => $automation,
            'contact' => $contact,
        ]);
    }

    /**
     * Automation tag contacts.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function tagContacts(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);

        // subscribers list
        if ($request->action_id) {
            $subscribers = Subscriber::gSearch($automation->subscribers($request->action_id), $request);
        } else {
            $subscribers = Subscriber::gSearch($automation->subscribers(), $request);
        }
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        // saving
        if ($request->isMethod('post')) {
            // make validator
            $validator = Validator::make($request->all(), [
                'tags' => 'required',
            ]);

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('automation2.contacts.tagContacts', [
                    'automation' => $automation,
                    'subscribers' => $subscribers,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // Copy to list
            foreach ($subscribers->get() as $subscriber) {
                $subscriber->addTags($request->tags);
            }

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.contacts.tagged', [
                    'count' => $subscribers->count(),
                ]),
            ], 201);
        }

        return view('automation2.contacts.tagContacts', [
            'automation' => $automation,
            'subscribers' => $subscribers,
        ]);
    }

    /**
     * Automation remove contact tag.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function removeTag(Request $request, $uid, $contact_uid)
    {
        $automation = Automation2::findByUid($uid);
        $contact = Subscriber::findByUid($contact_uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        $contact->removeTag($request->tag);

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.contact.tag.removed', [
                'tag' => $request->tag,
            ]),
        ], 201);
    }
    
    /**
     * Automation export contacts.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function exportContacts(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        // subscribers list
        if ($request->action_id) {
            $subscribers = Subscriber::gSearch($automation->subscribers($request->action_id), $request);
        } else {
            $subscribers = Subscriber::gSearch($automation->subscribers(), $request);
        }

        // saving
        if ($request->isMethod('post')) {
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.contacts.exported'),
            ], 201);
        }
    }

    /**
     * Automation copy contacts to new list.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function copyToNewList(Request $request, $uid)
    {
        $automation = Automation2::findByUid($uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        // subscribers list
        if ($request->action_id) {
            $subscribers = Subscriber::gSearch($automation->subscribers($request->action_id), $request);
        } else {
            $subscribers = Subscriber::gSearch($automation->subscribers(), $request);
        }

        // saving
        if ($request->isMethod('post')) {
            // make validator
            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('automation2.contacts.copyToNewList', [
                    'automation' => $automation,
                    'subscribers' => $subscribers,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // Crate new list
            $list = $automation->mailList->copy($request->name);

            // Copy to list
            foreach ($subscribers->get() as $subscriber) {
                $subscriber->copy($list);
            }

            // update cache
            $list->updateCache();

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.contacts.copied_to_new_list', [
                    'count' => $subscribers->count(),
                    'list' => $list->name,
                ]),
            ], 201);
        }

        return view('automation2.contacts.copyToNewList', [
            'automation' => $automation,
            'subscribers' => $subscribers,
        ]);
    }

    /**
     * Automation template classic builder.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateEditClassic(Request $request, $uid, $email_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($email_uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        // saving
        if ($request->isMethod('post')) {
            $rules = array(
                'content' => 'required',
            );
            
            $this->validate($request, $rules);
            
            $email->content = $request->content;
            $email->untransform();
            $email->save();

            // update email plain text
            $email->updatePlainFromContent();
            
            // update links
            $email->updateLinks();

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.email.content.updated'),
            ], 201);
        }

        return view('automation2.email.template.editClassic', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Automation template classic builder.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function templateEditPlain(Request $request, $uid, $email_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $email = Email::findByUid($email_uid);
        
        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        // saving
        if ($request->isMethod('post')) {
            $rules = array(
                'plain' => 'required',
            );
            
            // make validator
            $validator = Validator::make($request->all(), $rules);
            
            // redirect if fails
            if ($validator->fails()) {
                return response()->view('automation2.email.template.editPlain', [
                    'automation' => $automation,
                    'email' => $email,
                    'errors' => $validator->errors(),
                ], 400);
            }
            
            $email->plain = $request->plain;
            $email->untransform();
            $email->save();

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.automation.email.plain.updated'),
            ], 201);
        }

        return view('automation2.email.template.editPlain', [
            'automation' => $automation,
            'email' => $email,
        ]);
    }

    /**
     * Segment select.
     *
     * @return \Illuminate\Http\Response
     */
    public function segmentSelect(Request $request)
    {
        if (!$request->list_uid) {
            return '';
        }

        // init automation
        if ($request->uid) {
            $automation = Automation2::findByUid($request->uid);

            // authorize
            if (\Gate::denies('view', $automation)) {
                return $this->notAuthorized();
            }
        } else {
            $automation = new Automation2();

            // authorize
            if (\Gate::denies('create', $automation)) {
                return $this->notAuthorized();
            }
        }
        $list = MailList::findByUid($request->list_uid);
        
        return view('automation2.segmentSelect', [
            'automation' => $automation,
            'list' => $list,
        ]);
    }

    /**
     * Display a listing of subscribers.
     *
     * @return \Illuminate\Http\Response
     */
    public function subscribers(Request $request, $uid)
    {
        // init
        $automation = Automation2::findByUid($uid);
        $list = $automation->mailList;

        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        return view('automation2.subscribers.index', [
            'automation' => $automation,
            'list' => $list,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function subscribersList(Request $request, $uid)
    {
        // init
        $automation = Automation2::findByUid($uid);
        $list = $automation->mailList;

        // authorize
        if (\Gate::denies('update', $automation)) {
            return $this->notAuthorized();
        }

        $subscribers = $automation->subscribers()->search($request)
            ->where('mail_list_id', '=', $list->id);

        // $total = distinctCount($subscribers);
        $total = $subscribers->count();
        $subscribers->with(['mailList', 'subscriberFields']);
        $subscribers = \optimized_paginate($subscribers, $request->per_page, null, null, null, $total);

        $fields = $list->getFields->whereIn('uid', explode(',', $request->columns));

        return view('automation2.subscribers._list', [
            'automation' => $automation,
            'subscribers' => $subscribers,
            'total' => $total,
            'list' => $list,
            'fields' => $fields,
        ]);
    }

    /**
     * Remove subscriber from automation.
     *
     * @return \Illuminate\Http\Response
     */
    public function subscribersRemove(Request $request, $uid, $subscriber_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $subscriber = Subscriber::findByUid($subscriber_uid);

        // authorize
        if (\Gate::denies('update', $subscriber)) {
            return;
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.subscriber.removed'),
        ], 201);
    }

    /**
     * Restart subscriber for automation.
     *
     * @return \Illuminate\Http\Response
     */
    public function subscribersRestart(Request $request, $uid, $subscriber_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $subscriber = Subscriber::findByUid($subscriber_uid);

        // authorize
        if (\Gate::denies('update', $subscriber)) {
            return;
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.subscriber.restarted'),
        ], 201);
    }

    /**
     * Display a listing of subscribers.
     *
     * @return \Illuminate\Http\Response
     */
    public function subscribersShow(Request $request, $uid, $subscriber_uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);
        $subscriber = Subscriber::findByUid($subscriber_uid);

        // authorize
        if (\Gate::denies('read', $subscriber)) {
            return;
        }

        return view('automation2.subscribers.show', [
            'automation' => $automation,
            'subscriber' => $subscriber,
        ]);
    }
    
    /**
     * Get last saved time.
     *
     * @return \Illuminate\Http\Response
     */
    public function lastSaved(Request $request, $uid)
    {
        // init automation
        $automation = Automation2::findByUid($uid);

        // authorize
        if (\Gate::denies('view', $automation)) {
            return;
        }

        return trans('messages.automation.designer.last_saved', ['time' => $automation->updated_at->diffForHumans()]);
    }
}
