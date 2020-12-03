@extends('layouts.popup.small')

@section('content')
	<div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <form id="automationCreate" action="{{ action("Automation2Controller@create") }}" method="POST" class="form-validate-jqueryz">
                {{ csrf_field() }}            
        
                <h1 class="mb-20">{{ trans('messages.automation.create_automation') }}</h1>
            
                <p class="mb-10">{{ trans('messages.automation.name_your_automation') }}</p>
                
                <div class="row mb-4">
                    <div class="col-md-8">
                        @include('helpers.form_control', [
                            'type' => 'text',
                            'class' => '',
                            'label' => '',
                            'name' => 'name',
                            'value' => $automation->name,
                            'help_class' => 'automation',
                            'rules' => $automation->rules(),
                        ])
                    </div>
                </div>
    
                    
                <h3 class="mt-30 mb-20">{{ trans('messages.automation.choose_a_mail_list') }}</h3>
                <p class="mb-10">{{ trans('messages.automation.choose_a_mail_list.intro') }}</p>
                    
                <div class="row">
                    <div class="col-md-6">
                        @include('helpers.form_control', [
                            'name' => 'mail_list_uid',
                            'include_blank' => trans('messages.automation.choose_list'),
                            'type' => 'select',
                            'label' => '',
                            'value' => (is_object($automation->mailList) ? $automation->mailList->uid : ''),
                            'options' => Auth::user()->customer->readCache('MailListSelectOptions', []),
                            'rules' => $automation->rules(),
                        ])
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-10">
                        <div class="automation-segment">

                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <button class="btn btn-mc_primary mt-20">{{ trans('messages.automation.get_started') }}</button>
                </div>
                    
            </form>
                
        </div>
    </div>
        
    <script>
        // automation segment
        var automationSegment = new Box($('.automation-segment'));
        $('[name=mail_list_uid]').change(function(e) {
            var url = '{{ action('Automation2Controller@segmentSelect') }}?list_uid=' + $(this).val();

            automationSegment.load(url);
        });
        $('[name=mail_list_uid]').change();

        $('#automationCreate').submit(function(e) {
            e.preventDefault();
            
            var form = $(this);
            var url = form.attr('action');
            
            // loading effect
            createAutomationPopup.loading();
            
            $.ajax({
                url: url,
                method: 'POST',
                data: form.serialize(),
                statusCode: {
                    // validate error
                    400: function (res) {
                       createAutomationPopup.loadHtml(res.responseText);
                    }
                 },
                 success: function (res) {
                    createAutomationPopup.hide();
                    
                    addMaskLoading(res.message, function() {
                        setTimeout(function() {
                            window.location = res.url;
                        }, 1000);
                    });
                 }
            });
        });
    </script>
@endsection