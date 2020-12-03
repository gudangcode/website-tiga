@extends('layouts.popup.medium')

@section('content')
        
    @include('automation2.email._tabs', ['tab' => 'confirm'])
        
    <h5 class="mb-3">{{ trans('messages.automation.email.you_are_set_to_send') }}</h5>    
    <p>{{ trans('messages.automation.email.review_email_intro') }}</p>
    
    <form id="emailSetup" action="{{ action('Automation2Controller@emailSetup', $automation->uid) }}" method="POST">
        {{ csrf_field() }}
        
        <div class="row">
            <div class="col-md-7">
                @include('automation2.email._summary')
            </div>
        </div>                
            
        <a href="javascript:;" class="btn btn-secondary mt-4" onclick="sidebar.load(); popup.hide()">{{ trans('messages.close') }}</a>
    </form>
    
    <script>
    </script>
@endsection