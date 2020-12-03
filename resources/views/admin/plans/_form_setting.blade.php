<style>
    .hidden { display: 'none' }
</style>
<div class="row">
    <div class="col-md-7">
        <div class="mc_section">
            <h2>{{ trans('messages.plan.email_footer') }}</h2>
                
            <p>{{ trans('messages.plan.email_footer.intro') }}</p>
                
            <div class="form-group-checkboxes">                        
                @include('helpers.form_control', [
                    'class' => '',
                    'type' => 'checkbox2',
                    'name' => 'options[email_footer_enabled]',
                    'label' => trans('messages.plan.enabled_email_footer'),
                    'value' => $options['email_footer_enabled'],
                    'options' => ['no','yes'],
                    'help_class' => $help_class,
                    'rules' => $rules,
                ])
            </div>
            
            @include('helpers.form_control', [
                'class' => 'builder-editor',
                'type' => 'textarea',
                'name' => 'options[html_footer]',
                'label' => trans('messages.plan.html_footer'),
                'value' => $options['html_footer'],
                'help_class' => $help_class,
                'rules' => $rules,
            ])
            
            @include('helpers.form_control', [
                'class' => '',
                'type' => 'textarea',
                'name' => 'options[plain_text_footer]',
                'label' => trans('messages.plan.plain_text_footer'),
                'value' => $options['plain_text_footer'],
                'help_class' => $help_class,
                'rules' => $rules,
            ])
        </div>
    </div>
</div>
    
<script>
    $(document).ready(function() {
        // Sending domains checking setting
        $(document).on("change", "input[name='options[email_footer_enabled]']", function(e) {
            var email_footer_enabled = $('input[name="options[email_footer_enabled]"]:checked').val();
            console.log(email_footer_enabled);
            
            if (email_footer_enabled == 'yes') {
                $('input[name="options[email_footer_trial_period_only]"]').closest('.checkbox').removeClass('disabled');
                $('input[name="options[email_footer_trial_period_only]"]').removeAttr('disabled');
                tinymce.activeEditor.setMode('design');
                $('[name="options[plain_text_footer]"]').prop('readonly', false);
            } else {
                $('input[name="options[email_footer_trial_period_only]"]').closest('.checkbox').addClass('disabled');
                $('input[name="options[email_footer_trial_period_only]"]').attr('disabled', 'disabled');
                tinymce.activeEditor.setMode('readonly');
                $('[name="options[plain_text_footer]"]').prop('readonly', true);
            }
        });
        setTimeout(function() {
            $('input[name="options[email_footer_enabled]"]').trigger("change");
        }, 1000);
    });
</script>
