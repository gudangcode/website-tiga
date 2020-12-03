@include('automation2._info')
				
@include('automation2._tabs', ['tab' => 'statistics', 'sub' => 'timeline'])
    
<div class="timlines_list ajax-list"></div>
    
<script>
    var listTimeline = new List( $('.timlines_list'), {
        url: '{{ action('Automation2Controller@timelineList', [
                'uid' => $automation->uid,
            ]) }}',
        per_page: 5,
    });		
    listTimeline.load();
</script>