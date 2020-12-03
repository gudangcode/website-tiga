@extends('layouts.popup.medium')

@section('content')
	<div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <h2>{{ trans('messages.campaign.choose_your_template_layout') }}</h2>
                
            <ul class="nav nav-tabs mc-nav campaign-template-tabs">
                <li><a href="{{ action('CampaignController@templateLayout', $campaign->uid) }}">Layouts</a></li>
                <li class="active"><a href="{{ action('CampaignController@templateTheme', $campaign->uid) }}">Themes</a></li>
                <li><a href="{{ action('CampaignController@templateUpload', $campaign->uid) }}">Upload</a></li>
            </ul>
                
            <div id="gallery">
                <form class="listing-form"
                    data-url="{{ action('CampaignController@templateThemeList', $campaign->uid) }}"
                    per-page="{{ Acelle\Model\Template::$itemsPerPage }}"					
                >				
                    <div class="row top-list-controls">
                        <div class="col-md-9">		
                                <div class="filter-box">										
                                    <span class="filter-group">
                                        <span class="title text-semibold text-muted">{{ trans('messages.from') }}</span>
                                        <select class="select" name="from">
                                            <option value="all">{{ trans('messages.all') }}</option>
                                            <option value="mine">{{ trans('messages.my_templates') }}</option>
                                            <option value="gallery" selected='selected'>{{ trans('messages.gallery') }}</option>
                                        </select>										
                                    </span>
                                    <span class="filter-group">
                                        <span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>
                                        <select class="select" name="sort-order">
                                            <option value="custom_order" class="active">{{ trans('messages.custom_order') }}</option>
                                            <option value="name">{{ trans('messages.name') }}</option>
                                            <option value="created_at">{{ trans('messages.created_at') }}</option>
                                        </select>										
                                        <button class="btn btn-xs sort-direction" rel="asc" data-popup="tooltip" title="{{ trans('messages.change_sort_direction') }}" type="button" class="btn btn-xs">
                                            <i class="icon-sort-amount-asc"></i>
                                        </button>
                                    </span>
                                    <span class="text-nowrap">
                                        <input name="search_keyword" class="form-control search" placeholder="{{ trans('messages.type_to_search') }}" />
                                        <i class="icon-search4 keyword_search_button"></i>
                                    </span>
                                </div>
                        </div>
                    </div>
                    
                    <div class="pml-table-container">
                        
                        
                        
                    </div>
                </form>
            </div>
        </div>
    </div>
        
    <script>
        $('.campaign-template-tabs a').click(function(e) {
            e.preventDefault();
        
            var url = $(this).attr('href');
        
            templatePopup.load(url);
        });        
    </script>
@endsection