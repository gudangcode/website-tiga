@extends('layouts.automation.main')

@section('title', trans('messages.automation.create'))

@section('content')
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<style>
        rect.selected {
            stroke-width: 1 !important;;
            stroke-dasharray: 5;
        }

        rect.element {
            stroke:black;
            stroke-width:0;
        }

        rect.action {
            fill: rgb(101, 117, 138);
        }

        rect.trigger {
            fill: rgba(12, 12, 12, 0.49);
        }

        rect.wait {
            fill: #fafafa;
            stroke: #666;
            stroke-width: 1;
        }

        rect.operation {
            fill: #966089;
        }

        g.wait > g > a tspan {
            fill: #666;
        }

        rect.condition {
            fill: #e47a50;
        }

        g text:hover, g tspan:hover {
            fill: pink !important;
        }
    </style>
	
	<header>
		<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
			<a class="navbar-brand left-logo" href="#">
				@if (\Acelle\Model\Setting::get('site_logo_small'))
					<img src="{{ action('SettingController@file', \Acelle\Model\Setting::get('site_logo_small')) }}" alt="">
				@else
					<img height="22" src="{{ URL::asset('images/logo_light.png') }}" alt="">
				@endif
			</a>
			<div class="d-inline-block d-flex mr-auto align-items-center">
				<h1 class="">{{ $automation->name }}</h1>
				<i class="material-icons-outlined automation-head-icon ml-2">alarm</i>
			</div>
			<div class="automation-top-menu">
			<span class="mr-3"><i class="last_save_time" data-url="{{ action('Automation2Controller@lastSaved', $automation->uid) }}">{{ trans('messages.automation.designer.last_saved', ['time' => $automation->updated_at->diffForHumans()]) }}</i></span>
				<a href="{{ action('Automation2Controller@index') }}" class="action">
					<i class="material-icons-outlined mr-2">arrow_back</i>
					{{ trans('messages.automation.go_back') }}
				</a>

				<div class="switch-automation d-flex">
					<select class="select select2 top-menu-select" name="switch_automation">
						<option value="--hidden--"></option>
						@foreach($automation->getSwitchAutomations(Auth::user()->customer)->get() as $auto)
							<option value='{{ action('Automation2Controller@edit', $auto->uid) }}'>{{ $auto->name }}</option>
						@endforeach
					</select>

					<a href="javascript:'" class="action">
						<i class="material-icons-outlined mr-2">
	horizontal_split
	</i>
						{{ trans('messages.automation.switch_automation') }}
					</a>
				</div>

				<div class="account-info">
					<ul class="navbar-nav mr-auto navbar-dark bg-dark"">						
						<li class="nav-item dropdown">
							<a class="account-item nav-link dropdown-toggle px-2" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<img class="avatar" src="{{ action('CustomerController@avatar', Auth::user()->customer->uid) }}" alt="">
								{{ Auth::user()->customer->displayName() }}
							</a>
							<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
								@can("admin_access", Auth::user())
									<a class="dropdown-item d-flex align-items-center" href="{{ action("Admin\HomeController@index") }}">
										<i class="material-icons-outlined mr-2">double_arrow</i>
										{{ trans('messages.admin_view') }}
									</a>
									<div class="dropdown-divider"></div>
								@endif
								<a class="dropdown-item d-flex align-items-center quota-view" href="{{ action("AccountController@quotaLog2") }}">
									<i class="material-icons-outlined mr-2">multiline_chart</i>
									<span class="">{{ trans('messages.used_quota') }}</span>
								</a>
								<a class="dropdown-item d-flex align-items-center" href="{{ action('AccountSubscriptionController@index') }}">
									<i class="material-icons-outlined mr-2">redeem</i>
									<span>{{ trans('messages.subscriptions') }}</span>
								</a>
								<a class="dropdown-item d-flex align-items-center" href="{{ action("AccountController@profile") }}">
									<i class="material-icons-outlined mr-2">account_circle</i>
									<span>{{ trans('messages.account') }}</span>
								</a>
								@if (Auth::user()->customer->canUseApi())
									<a href="{{ action("AccountController@api") }}" class="dropdown-item d-flex align-items-center">
										<i class="material-icons-outlined mr-2">code</i>
										<span>{{ trans('messages.api') }}</span>
									</a>
								@endif
								<div class="dropdown-divider"></div>
								<a class="dropdown-item d-flex align-items-center" href="{{ url("/logout") }}">
									<i class="material-icons-outlined mr-2">power_settings_new</i>
									<span>{{ trans('messages.logout') }}</span>
								</a>
							</div>
						</li>
					</ul>
					
				</div>
			</div>
		</nav>
	</header>
	
	<main role="main">
		<div class="automation2">
			<div class="diagram text-center scrollbar-inner">				
				<svg id="svg" style="overflow: auto" width="3800" height="6800" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
					<text x="475" y="30" alignment-baseline="middle" text-anchor="middle">{{ trans('messages.automation.designer.intro') }}</text>

				</svg>

				<div class="history">
					<a class="history-action history-undo" href="javascript:;">
						<i class="material-icons-outlined">undo</i>
					</a>
					<a class="history-action history-redo disabled" href="javascript:;">
						<i class="material-icons-outlined">redo</i>
					</a>
					<a class="history-action history-list" href="javascript:;">
						<i class="material-icons-outlined">history</i>
					</a>
					<ul class="history-list-items">
						<li>
							<a href="" class="d-flex align-items-center current">
								<i class="material-icons-outlined mr-2">refresh</i>
								<span class="content mr-auto">Reset current flow</span>
								{{-- <time class="mini text-muted">1 minute</time> --}}
							</a>
						</li>
						<li class="divider"></li>
						<li>
							<a href="" class="d-flex align-items-center">
								<i class="material-icons-outlined mr-2">alarm</i>
								<span class="content mr-auto">Wait activity added</span>
								{{-- <time class="mini text-muted">3 hours</time> --}}
							</a>
						</li>
						<li>
							<a href="" class="d-flex align-items-center">
								<i class="material-icons-outlined mr-2">email</i>
								<span class="content mr-auto">Send email activity added</span>
								{{-- <time class="mini text-muted">4 days</time> --}}
							</a>
						</li>
						<li>
							<a href="" class="d-flex align-items-center">
								<i class="material-icons-outlined mr-2">call_split</i>
								<span class="content mr-auto">Condition activity added</span>
								{{-- <time class="mini text-muted">20 Aug</time> --}}
							</a>
						</li>
						<li>
							<a href="" class="d-flex align-items-center">
								<i class="material-icons-outlined mr-2">play_circle_outline</i>
								<span class="content mr-auto">Trigger criteria setup</span>
								{{-- <time class="mini text-muted">11 Aug</time> --}}
							</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="sidebar scrollbar-inner">
				<div class="sidebar-content">
					
				</div>
			</div>
		</div>
	</main>
		
	<script>
		var popup = new Popup(undefined, undefined, {
			onclose: function() {
				sidebar.load();
			}
		});

		var sidebar = new Box($('.sidebar-content'));
		var lastSaved = new Box($('.last_save_time'), $('.last_save_time').attr('data-url'));

		function toggleHistory() {
			var his = $('.history .history-list-items');

			if (his.is(":visible")) {
				his.fadeOut();
			} else {
				his.fadeIn();
			}
		}

		function openBuilder(url) {
			var div = $('<div class="full-iframe-popup">').html('<iframe scrolling="no" class="builder d-none" src="'+url+'"></iframe>');
            
            $('body').append(div);

            // open builder effects
            addMaskLoading("{{ trans('messages.automation.template.opening_builder') }}");
            $('.builder').on("load", function() {
                removeMaskLoading();

				$(this).removeClass("d-none");
            });
		}

		function openBuilderClassic(url) {
			var div = $('<div class="full-iframe-popup">').html('<iframe scrolling="yes" class="builder d-none" src="'+url+'"></iframe>');
            
            $('body').append(div);

            // open builder effects
            addMaskLoading("{{ trans('messages.automation.template.opening_builder') }}");
            $('.builder').on("load", function() {
                removeMaskLoading();

				$(this).removeClass("d-none");
            });
		}
		
		function setAutomationName(name) {
			$('.navbar h1').html(name);
		}
		
		function saveData(callback) {
			var url = '{{ action('Automation2Controller@saveData', $automation->uid) }}';
		
			$.ajax({
				url: url,
				type: 'POST',
				data: {
					_token: CSRF_TOKEN,
					data: JSON.stringify(tree.toJson()),
				},
			}).always(function() {
				if (callback != null) {
					callback();
				}

				// update last saved
				lastSaved.load();
			});
		}
		
		function SelectActionConfirm(key, insertToTree) {
			var url = '{{ action('Automation2Controller@actionSelectConfirm', $automation->uid) }}' + '?key=' + key;
			
			popup.load(url, function() {				
				// when click confirm select trigger type
				popup.popup.find('#action-select').submit(function(e) {
					e.preventDefault();
				
					var url = $(this).attr('action');
					var data = $(this).serialize();
					
					// show loading effect
					popup.loading();
					$.ajax({
						url: url,
						type: 'POST',
						data: data,
					}).always(function(response) {
						if (response.options.key == 'wait') {
							var newE = new ElementWait({title: response.title, options: response.options});							
						} else if (response.options.key == 'condition') {
							var newE = new ElementCondition({title: response.title, options: response.options});							
						}

						insertToTree(newE);

						newE.validate();
						
						// save tree
						saveData(function() {
							// hide popup
							popup.hide();
							
							notify('success', '{{ trans('messages.notify.success') }}', response.message);
						});
					});
				});
			});
		}

		function EmailSetup(id) {
			var url = '{{ action('Automation2Controller@emailSetup', $automation->uid) }}' + '?action_id=' + id;
			
			popup.load(url, function() {
				// set back event
				popup.back = function() {
					Popup.hide();
				};
			});
		}
	
		function OpenActionSelectPopup(insertToTree) {
			popup.load('{{ action('Automation2Controller@actionSelectPupop', $automation->uid) }}?hasChildren=' + tree.getSelected().hasChildren(), function() {
				console.log('Select action popup loaded!');
				
				// set back event
				popup.back = function() {
					Popup.hide();
				};
				
				// when click on action type
				popup.popup.find('.action-select-but').click(function() {
					var key = $(this).attr('data-key');

					if (key == 'send-an-email') {
						// new action as email
						var newE = new ElementAction({
							title: '{{ trans('messages.automation.tree.action_not_set') }}',
							options: {init: "false"}
						});
						
						// add email to tree
						insertToTree(newE);

						// validate
						newE.validate();

						// save tree
						saveData(function() {
							notify('success', '{{ trans('messages.notify.success') }}', '{{ trans('messages.automation.email.created') }}');
						});
					} else {
						// show select trigger confirm box
						SelectActionConfirm(key, insertToTree);
					}					
				});
			});
		}
		
		function OpenTriggerSelectPopup() {
			popup.load('{{ action('Automation2Controller@triggerSelectPupop', $automation->uid) }}', function() {
				console.log('Select trigger popup loaded!');
				
				// set back event
				popup.back = function() {
					Popup.hide();
				};
				
				// when click on trigger type
				popup.popup.find('.trigger-select-but').click(function() {
					var key = $(this).attr('data-key');
					
					// show select trigger confirm box
					SelectTriggerConfirm(key);
				});
			});
		}
		
		function SelectTriggerConfirm(key) {
			var url = '{{ action('Automation2Controller@triggerSelectConfirm', $automation->uid) }}' + '?key=' + key;
			
			popup.load(url, function() {
				console.log('Confirm trigger type popup loaded!');
				
				// set back event
				popup.back = function() {
					OpenTriggerSelectPopup();
				};
			});
		}
		
		function EditTrigger(url) {
			sidebar.load(url);
		}
		
		function EditAction(url) {
			sidebar.load(url);
		}
	
		$(document).ready(function() {
			// load sidebar
			sidebar.load('{{ action('Automation2Controller@settings', $automation->uid) }}');

			// history toggle
			$('.diagram .history .history-list').click(function() {
				toggleHistory();
			});
			$(document).mouseup(function(e) 
			{
				var container = $(".history .history-list-items");

				// if the target of the click isn't the container nor a descendant of the container
				if (!container.is(e.target) && container.has(e.target).length === 0) 
				{
					container.fadeOut();
				}
			});

			// switch automation
			$('[name=switch_automation]').change(function() {
				var val = $(this).val();
				var text = $('[name=switch_automation] option:selected').text();
				var confirm = "{{ trans('messages.automation.switch_automation.confirm') }} <span class='font-weight-semibold'>" + text + "</span>"; 

				var dialog = new Dialog('confirm', {
					message: confirm,
					ok: function(dialog) {
						window.location = val; 
					},
					cancel: function() {
						$('[name=switch_automation]').val('');
					},
					close: function() {
						$('[name=switch_automation]').val('');
					},
				});
			});
			$('.select2-results__option').each

			// fake history
			$('.diagram .history .history-list-items a, .history .history-undo').click(function(e) {
				e.preventDefault();

				var dialog = new Dialog('alert', {
					message: 'Automation is already finallized. Cannot rollback to previous state.',
				});
			});
			
			// quota view
			$('.quota-view').click(function(e) {
				e.preventDefault();

				var url = $(this).attr('href');

				popup.load(url, function() {
					console.log('quota popup loaded!');
				});
			});
		});
	</script>
		
	<script>
        var tree;

		function doSelect(e) {
			// TODO 1:
			// Gọi Ajax to Automation2@action
			// Prams: e.getId()
			// Trả về thông tin chi tiết của action để load nội dung bên phải
			// Trên server: gọi hàm model: Automation2::getActionInfo(id)
			
			e.select(); // highlight
			
			console.log(e.getType());
			
			// if click on a trigger
			if (e.getType() == 'ElementTrigger') {
				var options = e.getOptions();
				
				// check if trigger is not init
				if (options.init == "false") {
					OpenTriggerSelectPopup();
				}
				// trigger was init
				else {
					var url = '{{ action('Automation2Controller@triggerEdit', $automation->uid) }}' + '?key=' + e.getOptions().key + '&id=' + e.getId();
					
					// Open trigger types select list
					EditTrigger(url);
				}
			}
			// is WAIT
			else if (e.getType() == 'ElementWait') {
					var url = '{{ action('Automation2Controller@actionEdit', $automation->uid) }}' + '?key=' + e.getOptions().key + '&id=' + e.getId();
					
					// Open trigger types select list
					EditAction(url);
			}
			// is Condition
			else if (e.getType() == 'ElementCondition') {
					var url = '{{ action('Automation2Controller@actionEdit', $automation->uid) }}' + '?key=' + e.getOptions().key + '&id=' + e.getId();
					
					// Open trigger types select list
					EditAction(url);
			}
			// is Email
			else if (e.getType() == 'ElementAction') {
				if (e.getOptions().init == "true") {
					var type = $(this).attr('data-type');
					var url = '{{ action('Automation2Controller@email', $automation->uid) }}?email_uid=' + e.getOptions().email_uid;
					
					// Open trigger types select list
					EditAction(url);
				} else {
					// show select trigger confirm box
					EmailSetup(e.getId());
				}
			}
		}

        (function() {
            //var json = [
            //    {title: "Click to choose a trigger", id: "trigger", type: "ElementTrigger", options: {init: false}}
            //];
			
			@if ($automation->data)
				var json = {!! $automation->getData() !!};
			@else
				var json = [
					{title: "Click to choose a trigger", id: "trigger", type: "ElementTrigger", options: {init: "false"}}
				];
			@endif

            var container = document.getElementById('svg');

            tree = AutomationElement.fromJson(json, container, {
                onclick: function(e) {
                    doSelect(e);
                },

                onhover: function(e) {
                    console.log(e.title + " hovered!");
                },

                onadd: function(e) {
					e.select();
					OpenActionSelectPopup(function(element) {
						e.insert(element);
						e.getTrigger().organize();

						// select new element
						doSelect(element);
					});
                },

                onaddyes: function(e) {
                	e.select();
					OpenActionSelectPopup(function(element) {
						e.insertYes(element);
						e.getTrigger().organize();

						// select new element
						doSelect(element);
					});
                },

                onaddno: function(e) {
                	e.select();
					OpenActionSelectPopup(function(element) {
						e.insertNo(element);
						e.getTrigger().organize();

						// select new element
						doSelect(element);
					});
                },

                validate: function(e) {
                    if (e.getType() == 'ElementTrigger') {
                        if (e.getOptions()['init'] == null || !(e.getOptions()['init'] == "true" || e.getOptions()['init'] == true)) {
                            e.showNotice('{{ trans('messages.automation.trigger.is_not_setup') }}');
                            e.setTitle('{{ trans('messages.automation.trigger.is_not_setup.title') }}');
                        } else {
                            e.hideNotice();
                            // e.setTitle('Correct title goes here');
                        }
                    }

                    if (e.getType() == 'ElementAction') {
                        if (e.getOptions()['init'] == null || !(e.getOptions()['init'] == "true" || e.getOptions()['init'] == true)) {
							e.showNotice('{{ trans('messages.automation.email.is_not_setup') }}');
                            e.setTitle('{{ trans('messages.automation.email.is_not_setup.title') }}');
                        } else if (e.getOptions()['template'] == null || !(e.getOptions()['template'] == "true" || e.getOptions()['template'] == true)) {
							e.showNotice('{{ trans('messages.automation.email.has_no_content') }}');
                        } else {
                            e.hideNotice();
                            // e.setTitle('Correct title goes here');
                        }
                    }

                    if (e.getType() == 'ElementCondition') {
                        if (  e.getOptions()['type'] == null || 
                        	 (e.getOptions()['email'] == null && e.getOptions()['email_link'] == null )) {
							e.showNotice('Condition not set up yet');
                            e.setTitle('Condition not set up yet');
                        } else {
                            e.hideNotice();
                            // e.setTitle('Correct title goes here');
                        }
                    }
                }
            });

        })();
    </script>
@endsection
