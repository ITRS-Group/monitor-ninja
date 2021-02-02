$(function() {
  $("#setup_form").on('submit', function() {
    return check_setup();
  });

  $('div[excludeId=0]').css("display","block");
  $('div[excludeId=0]').removeClass("hide").addClass("active");
  $('body').on('change', '#exclude_date_block input', function(){
    thisid =$ (this).attr('excludeId');
    arr = thisid.split('-');
    var exclude_start = $('input[excludeId="exclude-date-start-'+arr[3]+'"]').val();
    var exclude_end = $('input[excludeId="exclude-date-end-'+arr[3]+'"]').val();
    var start_time = $('#fixed-duration-start-time').val();
    var date_now = new Date();
    var excludeStart = new Date(exclude_start+"T"+start_time);
    var excludeEnd = new Date(exclude_end+"T"+start_time);
    if(excludeEnd < excludeStart){
      $('input[excludeId="exclude-date-end-'+arr[3]+'"]').val(exclude_start);
    }

    if( $('input[excludeId="exclude-date-start-'+arr[3]+'"]').val() != '' && $('input[excludeId="exclude-date-end-'+arr[3]+'"]').val() != '' ){
      nextid = parseInt(arr[3])+1;
      $('div[excludeId='+ nextid +']').css("display","block");
      var close = '<span class="icon-16 x16-sign-cross remove_icon_exclude_date"></span>';
      $('span[excludeId="exclude-close-'+ arr[3] +'"]').html(close);
      $('div[excludeId='+ nextid +']').removeClass("hide").addClass("active");
    }
  });

  $('body').on('click', '.remove_icon_exclude_date', function(){
    $(this).closest('div').removeClass("active").addClass("hide").hide();
    summary_show();
  });

  var pathArray = window.location.pathname.split( '/' );
  if(pathArray[3] == "recurring_downtime" && pathArray[4] == "index"){
    if(!parseInt(_fixed)){
      var duration_arr = JSON.parse(_duration);
      $('#fixed').prop('checked', true);
      $('#duration-days').val(duration_arr.day);
      $('#duration-hours').val(duration_arr.hours);
      $('#duration-minutes').val(duration_arr.minutes);
    }else{
      $('#fixed').prop('checked', false);
    }
    $('#fixed-duration-start-time').val(_start_time);
    $('#fixed-duration-end-time').val(_end_time);
    $('#fixed-duration-start-date').val(_start_date);
    $('#fixed-duration-end-date').val(_end_date);
    var get_recurrence_on = JSON.parse(_recurrence_on);
        if(get_recurrence_on.day != "last"){
                localStorage.setItem('edit_no_box', 1);
                localStorage.setItem('edit_day_box', 1);
        }else{
                localStorage.setItem('edit_no_box', 0);
                localStorage.setItem('edit_day_box', 0);
        }
  }else{
    if(!$('#fixed-duration-start-date').val()){
      var date = new Date();
      var current_hour = date.getHours();
      var daten = (((current_hour+1) >= 23)? new Date(date.getTime() + 24 * 60 * 60 * 1000) : new Date());
      var clean_date = daten.getFullYear() + "-" + format_time(daten.getMonth()+1) +"-"+ format_time(daten.getDate());
      $('#fixed-duration-start-date').val(clean_date);
      $('#fixed-duration-end-date').val(clean_date);
      var default_hour = (((current_hour+1) >= 23)? 0 : (current_hour+1));
      $('#fixed-duration-start-time').val(format_time(default_hour)+':00');
      $('#fixed-duration-end-time').val(format_time(default_hour+1)+':00');
      endson_date = (date.getFullYear()+1) + "-" + format_time(date.getMonth()+1) +"-"+ format_time(date.getDate());
      $('#endson-date').val(endson_date);
    }
  }

  localStorage.setItem('start_date', JSON.stringify($('#fixed-duration-start-date').val()));
  localStorage.setItem('end_date', JSON.stringify($('#fixed-duration-end-date').val()));
  localStorage.setItem('start_time', JSON.stringify($('#fixed-duration-start-time').val()));
  localStorage.setItem('end_time', JSON.stringify($('#fixed-duration-end-time').val()));
  localStorage.setItem('ends_on', JSON.stringify($('#endson-date').val()));

  $('.content').on('click', '.recurring_delete', function(ev) {
    ev.preventDefault();
    var this_id = $(this).data('recurring-id');
    if (confirm(_('Are you sure that you would like to delete this schedule.\nPlease note that already scheuled downtime won\'t be affected by this and will have to be deleted manually.\nThis action can\'t be undone.'))) {
      $.ajax({
        url:_site_domain + _index_page + '/recurring_downtime/delete',
        type: 'POST',
        data: {
          schedule_id: this_id,
          csrf_token: _csrf_token
        },
        success: function(data) {
          if (data) {
            $.notify(data);
            window.setTimeout(function() {
              lsfilter_main.refresh();
            }, 1500);
          }
          else {
            $.notify('An unexpected error occured', {'sticky':true});
          }
        },
        error: function(){
          $.notify("An unexpected error occured", {'sticky':true});
        }
      });
    }
       return false;
  });

  $('#fixed').on('change', function() {
    if ($(this).is(':checked')){
      $('#rec-flexible-part').show();
      $('#rec-fixed-part .label').html("Start between");
      $('#flexible-help-text').show();
      $('#fixed-duration-text').hide();
    }else{
      $('#rec-flexible-part').hide();
      $('#rec-fixed-part .label').html("Downtime duration");
      $('#flexible-help-text').hide();
      $('#fixed-duration-text').show();
    }
  }).each(function() {
    if ($(this).is(':checked')){
      $('#rec-flexible-part').show();
    }
    else{
      $('#rec-flecible-part').hide();
    }
  });

  $('#recurrence').on('change', function() {
    if($(this).val() == 'custom'){
      $('.recurrence').show();
      if($('.repeat-text').val() == "week"){
        $('.recurrence-on').show();
        $('#recurrence-on-week').show();
      }
      if($('.repeat-text').val() == "month"){
        $('.recurrence-on').show();
        $('#recurrence-on-month').show();
      }
      if($('.repeat-text').val() == "year"){
        $('.recurrence-on').show();
        $('#recurrence-on-year').show();
      }
    }else{
      if($(this).val() == 'no'){
        $('.recurrence').hide();
        $('.recurrence-on').hide();
      }else{
        var quick_option_no = $('#recurrence').find('option:selected').attr('no');
        $('.recurrence-on').hide();
        $('#recurrence-on-week').hide();
        $('#recurrence-on-month').hide();
        $('#recurrence-on-year').hide();
        if(quick_option_no == 2){
          $('.recurrence').show();
        }
        if(quick_option_no == 3){
          $('.recurrence').show();
          $('select[name="recurrence_text"]').find('option:contains("Week")').attr("selected",true);
          $('.recurrence-on').show();
          $('#recurrence-on-week').show();
        }
        if(quick_option_no == 4){
          $('.recurrence').show();
          $('select[name="recurrence_text"]').find('option:contains("Month")').attr("selected",true);
          $('#rec-on-any-day-month').prop("checked",true);
          $('.recurrence-on').show();
          $('#recurrence-on-month').show();
        }
        if(quick_option_no == 6){
          $('.recurrence').show();
          $('select[name="recurrence_text"]').find('option:contains("Month")').attr("selected",true);
          $('#rec-on-last-day-month').prop("checked",true);
          $('.recurrence-on').show();
          $('#recurrence-on-month').show();
        }
      }
    }
  });

  $('.recurrence .repeat-text').on('change', function() {
    if($(this).val() == "week"){
      $('.recurrence-on').show();
      $('#recurrence-on-week').show();
    }else{
      $('#recurrence-on-week').hide();
    }
    if($(this).val() == "month"){
      $('.recurrence-on').show();
      $('#recurrence-on-month').show();
    }else{
      $('#recurrence-on-month').hide();
    }
    if($(this).val() == "year"){
      $('.recurrence-on').show();
      $('#recurrence-on-year').show();
    }else{
      $('#recurrence-on-year').hide();
    }
    if($(this).val() == "day"){
      $('.recurrence-on').hide();
    }
  });

  $(document).on('change', 'input[name="recurrence_no"]', function() {
    $('select[name="recurrence_select"]').find('option:contains("Custom")').attr("selected",true);
  });
  $(document).on('change', 'select[name="recurrence_text"]', function() {
    $('select[name="recurrence_select"]').find('option:contains("Custom")').attr("selected",true);
  });
  $(document).on('click', '#rec-on-week-days span', function() {
    $('select[name="recurrence_select"]').find('option:contains("Custom")').attr("selected",true);
  });
  $(document).on('change', '#recurrence-on-month', function() {
    $('select[name="recurrence_select"]').find('option:contains("Custom")').attr("selected",true);
  });
  $(document).on('change', '#rec-on-last-day-month', function() {
    $('select[name="recurrence_select"]').find('option:contains("Custom")').attr("selected",true);
  });
  $(document).on('change', 'input[name="ends"]', function() {
    $('select[name="recurrence_select"]').find('option:contains("Custom")').attr("selected",true);
  });
  $(document).on('change', '#exclude_date_block input', function() {
    $('select[name="recurrence_select"]').find('option:contains("Custom")').attr("selected",true);
  });

  $('#progress').css('position', 'absolute').css('top', '90px').css('left', '470px');

  $('#select-all-days').on('click', function() {
    $('.recurring_day').prop('checked', true);
  });

  $('#deselect-all-days').on('click', function() {
    $('.recurring_day').prop('checked', false);
  });

  $('#select-all-months').on('click', function() {
    $('.recurring_month').prop('checked', true);
  });

  $('#deselect-all-months').on('click', function() {
    $('.recurring_month').prop('checked', false);
  });

  $('input.date-picker').datepicker({
    firstDay: 1,
    dateFormat: "yy-mm-dd",
    minDate: 0
  });

  $('#fixed-duration-start-time').on('click', function(e){
    $('.starttime-quickselect').html(quickselect_data($(this).val(),"s"));
    $('.starttime-quickselect').show();
    document.getElementById('starttime-options').scrollTop = document.getElementById('s'+$(this).val()).offsetTop;
    e.stopPropagation();
  });

  $('.starttime-quickselect').on('click', 'div.time', function() {
    var selected_time = $(this).html();
    $('#fixed-duration-start-time').val(selected_time);
    $('.starttime-quickselect').hide();
    $( ".fixed-duration-part" ).trigger( "change" );
  });

  $('#fixed-duration-end-time').on('click', function(e){
    $('.endtime-quickselect').html(quickselect_data($(this).val(),"e"));
    $('.endtime-quickselect').show();
    document.getElementById('endtime-options').scrollTop = document.getElementById('e'+$(this).val()).offsetTop;
    e.stopPropagation();
  });

  $('.endtime-quickselect').on('click', 'div.time', function() {
    var selected_time = $(this).html();
    $('#fixed-duration-end-time').val(selected_time);
    $('.endtime-quickselect').hide();
    $( ".fixed-duration-part" ).trigger( "change" );
  });

  $(document).on('click', function(){
    $('.quickselect').hide();
  });

  $('body').on('change', '#rec-on-no-box', function(){
    if($('#rec-on-any-day-month').is(':checked')){
      var selected_day = $('#rec-on-day-box').val();
      var selected_day_no = $(this).val();
      if($('.repeat-text').val() == "month"){
        if(pathArray[3] == "recurring_downtime" && pathArray[4] == "index"){
                        var edit_no_box = localStorage.getItem('edit_no_box');
                        if(edit_no_box != 1){
                                set_startdate(selected_day, selected_day_no, 0);
                        }
                        localStorage.setItem('edit_no_box', 0);
                }else{
                        set_startdate(selected_day, selected_day_no, 0);
                }
      }
    }
  })

  $('body').on('change', '#rec-on-day-box', function(e){
    if($('#rec-on-any-day-month').is(':checked')){
      var selected_day_no = $('#rec-on-no-box').val();
      var selected_day = $(this).val();
      if($('.repeat-text').val() == "month"){
        if(pathArray[3] == "recurring_downtime" && pathArray[4] == "index"){
                        var edit_day_box = localStorage.getItem('edit_day_box');
                        if(edit_day_box != 1){
                                set_startdate(selected_day, selected_day_no, 0);
                        }
                        localStorage.setItem('edit_day_box', 0);
                }else{
                        set_startdate(selected_day, selected_day_no, 0);
                }
      }
    }
  });

  $('body').on('change', '#rec-on-any-day-month', function(e){
    if($(this).is(':checked')){
      var selected_day_no = $('#rec-on-no-box').val();
      var selected_day = $('#rec-on-day-box').val();
      if($('.repeat-text').val() == "month"){
        set_startdate(selected_day, selected_day_no, 0);
      }
    }
  });

  $('body').on('change', '#rec-on-last-day-month', function(){
    if($(this).is(':checked')){
      if($('.repeat-text').val() == "month"){
        set_startdate("last", "last", 0);
      }
    }
  });

  function set_startdate(selected_day, selected_day_no, next_month){
    var start_time = $('#fixed-duration-start-time').val();
    var start_date = $('#fixed-duration-start-date').val();
    var date_now = new Date();
    var startDate = new Date(start_date+"T"+start_time);
    var day = startDate.getDay();
    var date = startDate.getDate();
    var month = startDate.getMonth();
    var year = startDate.getYear();
    var days_name = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
    var day_name = days_name[startDate.getDay()];
    var selected_day = selected_day;
    var selected_day_no = selected_day_no;
    if(selected_day == "last" && selected_day_no == "last"){
      startDate.setDate(1);
      startDate.setMonth(month+1);
      startDate.setDate(startDate.getDate()-1);
    }else{
      $.each(days_name, function(key, value) {
        if(value == selected_day){
          day = key;
        }
      });
      startDate.setMonth(month + next_month);
      startDate.setDate(1);
      while (startDate.getDay() !== day) {
        startDate.setDate(startDate.getDate() + 1);
      }
      switch(selected_day_no){
        case "first": startDate.setDate(startDate.getDate() + 0); break;
        case "second": startDate.setDate(startDate.getDate() + 7); break;
        case "third": startDate.setDate(startDate.getDate() + 14); break;
        case "fourth": startDate.setDate(startDate.getDate() + 21); break;
        default: startDate.setDate(startDate.getDate() + 0);
      }
      if(selected_day_no == "last"){
        startDate.setDate(startDate.getDate() + 28);
        if(startDate.getMonth() != month){
          startDate.setDate(startDate.getDate() - 7);
        }
      }
      if(startDate < date_now){
        set_startdate(selected_day, selected_day_no, 1);
        return;
      }
      var day = startDate.getDay();
      var date = startDate.getDate();
      var month = startDate.getMonth();
      var days_name = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
      var month_name = ["January","February","March","April","May","June","July","August","September","October","November","December"];
      var day_name = days_name[startDate.getDay()];
      day_no = Math.ceil(date/7);
      if(selected_day_no == "last"){
        day_no = "last";
      }
      var any_day_month_val = JSON.stringify({"day_no":day_no,"day":day});
      $('#rec-on-any-day-month').val(any_day_month_val);
    }

    var clean_date = startDate.getFullYear() + "-" + format_time(startDate.getMonth()+1) +"-"+ format_time(startDate.getDate());
    $('#fixed-duration-start-date').val(clean_date);
    $('#fixed-duration-end-date').val(clean_date);
    if(selected_day_no == "last" && selected_day == "last"){
      $('input[name="month_on"][no=3]').prop("checked",true);
    }else{
      if(selected_day_no == "last"){
        $('select[name="rec-on-no-box"]').find('option:contains("last")').attr("selected",true);
      }
    }
    summary_show();
  }

  $('body').on('click', '#rec-on-week-days span', function(){
    var pre_day = $('input[name="week_on_day[]"]:checked').attr("wno");
    var day = $(this).attr("tag");
    if($('input[tag="week-'+ day +'"]').prop("checked")){
      $('input[tag="week-'+ day +'"]').prop("checked",false);
      $(this).css("background-color","transparent").css("color","black");
      if(!$('input[name="week_on_day[]"]:checked').val()){
        $('input[tag="week-'+ day +'"]').prop("checked",true);
        $(this).css("background-color","#0277BD").css("color","white");
      }
    }else{
      $('input[tag="week-'+ day +'"]').prop("checked",true);
      $(this).css("background-color","#0277BD").css("color","white");
    }
    var post_day = $('input[name="week_on_day[]"]:checked').attr("wno");
    if(pre_day != post_day){
      set_week_startdate(pre_day, post_day);
    }
  });

  function set_week_startdate(pre_day, post_day){
    var start_time = $('#fixed-duration-start-time').val();
    var start_date = $('#fixed-duration-start-date').val();
    var date_now = new Date();
    var startDate = new Date(start_date+"T"+start_time);
    var day = startDate.getDay();
    day_diff= day-post_day;
    startDate.setDate(startDate.getDate()-day_diff);
    if(startDate < date_now){
      startDate.setDate(startDate.getDate()+7);
    }
    var clean_date = startDate.getFullYear() + "-" + format_time(startDate.getMonth()+1) + "-" + format_time(startDate.getDate());
    $('#fixed-duration-start-date').val(clean_date);
    $('#fixed-duration-end-date').val(clean_date);
    var week_day_select_1 = $('input[name="week_on_day[]"][wno=1]').prop('checked');
    var week_day_select_2 = $('input[name="week_on_day[]"][wno=2]').prop('checked');
    var week_day_select_3 = $('input[name="week_on_day[]"][wno=3]').prop('checked');
    var week_day_select_4 = $('input[name="week_on_day[]"][wno=4]').prop('checked');
    var week_day_select_5 = $('input[name="week_on_day[]"][wno=5]').prop('checked');
    var week_day_select_6 = $('input[name="week_on_day[]"][wno=6]').prop('checked');
    var week_day_select_0 = $('input[name="week_on_day[]"][wno=0]').prop('checked');
    $( ".fixed-duration-part" ).trigger( "change" );
    $.each([ 1,2,3,4,5,6,0 ], function( index, value ) {
      if(eval('week_day_select_'+ value +'')){
        $('input[name="week_on_day[]"][wno="'+ value +'"]').prop("checked",true);
        $('span[wno="'+ value +'"]').css("background-color","#0277BD").css("color","white");
      }else{
        $('input[name="week_on_day[]"][wno="'+ value +'"]').prop("checked",false);
        $('span[wno="'+ value +'"]').css("background-color","transparent").css("color","black");
      }
    });
  }

  function set_endTime(){
    var start_time = $('#fixed-duration-start-time').val();
    var start_date = $('#fixed-duration-start-date').val();
    var end_time = $('#fixed-duration-end-time').val();
    var end_date = $('#fixed-duration-end-date').val();
    var startDate = new Date(start_date+"T"+start_time);
    var endDate = new Date(end_date+"T"+end_time);
    var timeDiff = endDate-startDate;
    if(timeDiff < 0){
      $('#fixed-duration-end-time').val(start_time);
      $('#fixed-duration-end-date').val(start_date);
    }else{
      return true;
    }
  };

  $('.fixed-duration-part').on('change', function() {
    set_endTime();
    var start_time = $('#fixed-duration-start-time').val();
    var start_date = $('#fixed-duration-start-date').val();
    var end_time = $('#fixed-duration-end-time').val();
    var end_date = $('#fixed-duration-end-date').val();
    if(start_time == ''){
      pre_start_time = JSON.parse(localStorage.getItem('start_time'));
      $('#fixed-duration-start-time').val(pre_start_time);
    }

    if(end_time == ''){
      pre_end_time = JSON.parse(localStorage.getItem('end_time'));
      $('#fixed-duration-end-time').val(pre_end_time);
    }

    if(start_date == ''){
      pre_start_date = JSON.parse(localStorage.getItem('start_date'));
      $('#fixed-duration-start-date').val(pre_start_date);
    }

    if(end_date == ''){
      pre_end_date = JSON.parse(localStorage.getItem('end_date'));
      $('#fixed-duration-end-date').val(pre_end_date);
    }

    var startDate = new Date(start_date+"T"+start_time);
    var endDate = new Date(end_date+"T"+end_time);
    var timeDiff = (endDate-startDate)/1000;
    var d = Math.floor(timeDiff/(3600*24));
    var h = Math.floor((timeDiff%(3600*24))/3600);
    var m = Math.floor((timeDiff%3600)/60);
    var duration_string = ((d != 0) ? d +' days ': '' );
    duration_string += ((h != 0) ? h +' hours ': '' );
    duration_string += ((m != 0) ? m +' minutes ': '' );
    if (!$('#fixed').is(':checked')){
      if(start_time !='' && start_date !='' && end_time !='' && end_date !=''){
        $('#fixed-duration-text').html('Downtime duration '+ duration_string);
      }
    }else{
      var duration_days = $('#duration-days').val();
      var duration_hours = $('#duration-hours').val();
      var duration_minutes = $('#duration-minutes').val();
      var f_duration_string = ((duration_days!=0) ? duration_days +' days ': ' 0 days ' );
      f_duration_string += ((duration_hours!=0) ? duration_hours +' hours ': ' 0 hours ' );
      f_duration_string += ((duration_minutes!=0) ? duration_minutes+' minutes ': ' 0 minutes ' );
    }
    var day = startDate.getDay();
    var date = startDate.getDate();
    var month = startDate.getMonth();
    var days_name = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
    var month_name = ["January","February","March","April","May","June","July","August","September","October","November","December"];
    var day_name = days_name[startDate.getDay()];
    day_no = Math.ceil(date/7);
    day_conv = ['','first','second','third','fourth','fifth'];
    lastDay = new Date(startDate);
    lastDay.setDate(startDate.getDate() + 1);
    if(lastDay.getMonth() != month){
      var last_day_option_quick = '<option no="6" value=\''+ JSON.stringify({"recur":{"label":"quick","no":1,"text":"month"},"on":{"day_no":"last","day":"last"}}) +'\'>Monthly on the last day</option>';
      var last_day_option_custom = '<div><input id="rec-on-last-day-month" no="3" editattr="lastmonthday" type="radio" name="month_on" value=\''+ JSON.stringify({"day_no":"last","day":"last"}) +' \'> the last day of the month</div>';
    }else{
      var last_day_option_quick = '';
      var last_day_option_custom = '<div><input id="rec-on-last-day-month" no="3" editattr="lastmonthday" type="radio" name="month_on" value=\''+ JSON.stringify({"day_no":"last","day":"last"}) +' \'> the last day of the month</div>';
    }
    var quick_option_no = $('#recurrence').find('option:selected').attr('no');

    if(day_no == 5){
      var quick_day_no = "last";
    }else{
      var quick_day_no = format_date(day_no);
    }

    $('#recurrence').html('\
      <option no="1" value="no">Choose recurrence</option> \
      <option no="3" value=\'' + JSON.stringify({"recur":{"label":"quick","no":1,"text":"week"},"on":[{"day":day}]}) + '\'>Weekly on ' + day_name + '</option> \
      <option no="4" value=\'' + JSON.stringify({"recur":{"label":"quick","no":1,"text":"month"},"on":{"day_no":day_no,"day":day}}) + '\'>Monthly on the ' + quick_day_no + ' ' + day_name + '</option>' + last_day_option_quick + ' \
      <option no="5" value="custom">Custom recurrence</option> \
      ');

    if(quick_option_no == 2){
      $('select[name="recurrence_select"]').find('option[no=2]').attr("selected",true);
    }else if(quick_option_no == 3){
      $('select[name="recurrence_select"]').find('option[no=3]').attr("selected",true);
    }else if(quick_option_no == 4){
      $('select[name="recurrence_select"]').find('option[no=4]').attr("selected",true);
    }else if(quick_option_no == 5){
      $('select[name="recurrence_select"]').find('option[no=5]').attr("selected",true);
    }else if(quick_option_no == 6){
      if($('select[name="recurrence_select"]').find('option[no=6]').text() == ''){
        $('select[name="recurrence_select"]').find('option[no=4]').attr("selected",true);
      }else{
        $('select[name="recurrence_select"]').find('option[no=6]').attr("selected",true);
      }
    }

    var custom_year_option_no = $('input[name=year_on]:checked').attr('no');
    $('#recurrence-on-year').html('\
      <div><input no="1" editattr="dayweekday" class="repeat_on" checked="checked" type="radio" name="year_on" value=\'' + JSON.stringify({"day_no":day_no,"day":day,"month":month}) + ' \'> the ' + format_date(day_no) + ' ' + day_name + ' of ' + month_name[month] + '</div>\
      <div><input no="2" editattr="lastweekday" class="repeat_on" type="radio" name="year_on" value=\'' + JSON.stringify({"day_no":"last","day":day,"month":month}) + ' \'> the last ' + day_name + ' of ' + month_name[month] + '</div>\
      ');

    if(custom_year_option_no == 2){
      $('input[name="year_on"][no=2]').prop("checked",true);
    }

    var custom_month_option_no = $('input[name=month_on]:checked').attr('no');
    $('#recurrence-on-month').html('\
      <div class="relative"><input id="rec-on-any-day-month" no="1" editattr="dayweekday" class="repeat_on" checked="checked" type="radio" name="month_on" value=\'' + JSON.stringify({"day_no":day_no,"day":day}) + ' \'> \
      the <select name="rec-on-no-box" id="rec-on-no-box" class="rec-on-no-box" value="'+ format_num_word(day_no) +'"> \
      <option>first</option><option>second</option><option>third</option><option>fourth</option><option>last</option></select> \
      <select name="rec-on-day-box" id="rec-on-day-box" class="rec-on-day-box" value="'+ day_name +'"> \
      <option>Monday</option><option>Tuesday</option><option>Wednesday</option><option>Thursday</option><option>Friday</option><option>Saturday</option><option>Sunday</option></select>' + last_day_option_custom + '\
      ');

    $('select[name="rec-on-no-box"]').find('option:contains('+ format_num_word(day_no) +')').attr("selected",true);
    $('select[name="rec-on-day-box"]').find('option:contains('+ day_name +')').attr("selected",true);

    $('#recurrence-on-week').html('\
      <div id="rec-on-week-days"><input class="hide" checked="checked" type="radio" name="week_on" value=\'' + JSON.stringify({"day":day}) + ' \'> \
      <input wno=1 name="week_on_day[]" tag="week-Monday" type="checkbox" value=\'' + JSON.stringify({"day":1}) + ' \'><span wno=1 tag="Monday">Mon</span> \
      <input wno=2 name="week_on_day[]" tag="week-Tuesday" type="checkbox" value=\'' + JSON.stringify({"day":2}) + ' \'><span wno=2 tag="Tuesday">Tue</span> \
      <input wno=3 name="week_on_day[]" tag="week-Wednesday" type="checkbox" value=\'' + JSON.stringify({"day":3}) + ' \'><span wno=3 tag="Wednesday">Wed</span> \
      <input wno=4 name="week_on_day[]" tag="week-Thursday" type="checkbox" value=\'' + JSON.stringify({"day":4}) + ' \'><span wno=4 tag="Thursday">Thu</span> \
      <input wno=5 name="week_on_day[]" tag="week-Friday" type="checkbox" value=\'' + JSON.stringify({"day":5}) + ' \'><span wno=5 tag="Friday">Fri</span> \
      <input wno=6 name="week_on_day[]" tag="week-Saturday" type="checkbox" value=\'' + JSON.stringify({"day":6}) + ' \'><span wno=6 tag="Saturday">Sat</span> \
      <input wno=0 name="week_on_day[]" tag="week-Sunday" type="checkbox" value=\'' + JSON.stringify({"day":0}) + ' \'><span wno=0 tag="Sunday">Sun</span></div>\
      ');

    $('input[name="week_on_day[]"][wno="'+ day +'"]').prop("checked",true);
    $('span[wno="'+ day +'"]').css("background-color","#0277BD").css("color","white");

    if(timeDiff <= 0){
      $('.note').css('background-color','#F7E650');
      $('.note').css('color','#212121');
      if (!$('#fixed').is(':checked')){
        $('.note-warning').html('NOTE: Invalid Downtime Duration');
      }else{
        $('.note-warning').html('NOTE: Invalid Start between Duration');
      }
    }else{
      $('.note').css('background-color','#0277BD');
      $('.note').css('color','white');
      $('.note-warning').html('');
    }
  });

  $('.recurring-downtime-form input').on('change', function() {
    summary_show();
  });

  $('.recurring-downtime-form select').on('change', function() {
    summary_show();
  });

  $(document).on('change', '.recurring-downtime-form input', function() {
    summary_show();
  });

  $(document).on('change', '#exclude_date_block input', function() {
    summary_show();
  });

  $(document).on('click', '#rec-on-week-days span', function() {
    summary_show();
  });

  $( ".fixed-duration-part" ).trigger( "change" );
});

$(function() {
  var pathArray = window.location.pathname.split( '/' );
  if(pathArray[3] == "recurring_downtime" && pathArray[4] == "index"){
    var get_recurrence = JSON.parse(_recurrence);
    var get_recurrence_on = JSON.parse(_recurrence_on);
    var get_exclude_days = _exclude_days;
    if(get_recurrence.label == 'quick'){
      if(get_recurrence.text == 'day'){
        $('select[name="recurrence_select"]').find('option:contains("Daily")').attr("selected",true);
      }

      if(get_recurrence.text == 'month'){
        if(get_recurrence_on.day_no == "last" && get_recurrence_on.day == "last"){
          $('select[name="recurrence_select"]').find('option:contains("last")').attr("selected",true);
        }else{
          $('select[name="recurrence_select"]').find('option:contains("Monthly")').attr("selected",true);
        }
      }

      if(get_recurrence.text == 'week'){
        $('select[name="recurrence_select"]').find('option:contains("Weekly")').attr("selected",true);
      }

    }else if(get_recurrence.label == 'custom'){
      $('select[name="recurrence_select"]').find('option:contains("Custom")').attr("selected",true);
    }

    $('input[name="recurrence_no"]').val(get_recurrence.no);
    if(get_recurrence.text == 'day'){
      $('select[name="recurrence_text"]').find('option:contains("Day")').attr("selected",true);
    }

    if(get_recurrence.text == 'week'){
      $('select[name="recurrence_text"]').find('option:contains("Week")').attr("selected",true);
      $.each(get_recurrence_on, function (key, value) {
        $('#rec-on-week-days span[wno='+value.day+']').trigger("click");
      })
    }

    if(get_recurrence.text == 'month'){
      $('select[name="recurrence_text"]').find('option:contains("Month")').attr("selected",true);
      if(get_recurrence_on.day_no == "last" && get_recurrence_on.day != "last"){
        $('input[name="month_on"][editattr="lastweekday"]').prop("checked",true);
      }else if(get_recurrence_on.day_no == "last" && get_recurrence_on.day == "last"){
        $('input[name="month_on"][editattr="lastmonthday"]').prop("checked",true);
      }else{
        $('input[name="month_on"][editattr="dayweekday"]').prop("checked",true);

      }
    }

    if(get_recurrence.text == 'year'){
      $('select[name="recurrence_text"]').find('option:contains("Year")').attr("selected",true);
      if(get_recurrence_on.day_no == "last" && get_recurrence_on.day != "last"){
        $('input[name="year_on"][editattr="lastweekday"]').prop("checked",true);
      }else if(get_recurrence_on.day_no == "last" && get_recurrence_on.day == "last"){
        $('input[name="year_on"][editattr="lastmonthday"]').prop("checked",true);
      }else{
        $('input[name="year_on"][editattr="dayweekday"]').prop("checked",true);
      }
    }

    if(_recurrence_ends == 0){
      var startDate = new Date(_start_date + "T" + _start_time);
      $('input[name="ends"][value="never"]').prop("checked",true);
      endson_date = (startDate.getFullYear()+1) + "-" + format_time(startDate.getMonth()+1) + "-" + format_time(startDate.getDate());
      $('#endson-date').val(endson_date);
    }else{
      $('input[name="ends"][value="finite_ends"]').prop("checked",true);
      $('input[name="finite_ends_value"]').val(_recurrence_ends);
    }

    $('#exclude_days_all').val(get_exclude_days);
    var arr_exclude_days = get_exclude_days.split(',');
    var i = 0;
    $.each(arr_exclude_days, function(key, value){
      var arr_day = value.split('to');
      $('input[excludeId="exclude-date-start-'+i+'"]').val(arr_day[0]);
      if(arr_day[1] == ''){
        $('input[excludeId="exclude-date-end-'+i+'"]').val(arr_day[0]);
      }else{
        $('input[excludeId="exclude-date-end-'+i+'"]').val(arr_day[1]);
      }
      $('input[excludeId="exclude-date-end-'+i+'"]').trigger('change');
      i += 1;
    })
    $( ".recurring-downtime-form select" ).trigger( "change" );
  }
});

function summary_show(){
  return; // Don't render the summary.

  var flexible = $('#fixed').attr('checked');
  var start_time = $('#fixed-duration-start-time').val();
  var end_time = $('#fixed-duration-end-time').val();
  var start_date = $('#fixed-duration-start-date').val();
  var end_date = $('#fixed-duration-end-date').val();
  var duration_days = $('#duration-days').val();
  var duration_hours = $('#duration-hours').val();
  var duration_minutes = $('#duration-minutes').val();
  var f_duration_string = ((duration_days != 0) ? duration_days +' days ': '' );
  f_duration_string += ((duration_hours != 0) ? duration_hours +' hours ': '' );
  f_duration_string += ((duration_minutes != 0) ? duration_minutes+' minutes ': '' );
  f_duration_string = ((f_duration_string != '') ? f_duration_string : '0 hours');
  var startDate = new Date(start_date+"T"+start_time);
  var endDate = new Date(end_date+"T"+end_time);
  var day = startDate.getDay();
  var date = startDate.getDate();
  var month = startDate.getMonth();
  var timeDiff = (endDate-startDate)/1000;
  var days_name = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
  var month_name = ["January","February","March","April","May","June","July","August","September","October","November","December"]
  var day_conv = ['','first','second','third','fourth','fifth'];
  var day_name = days_name[startDate.getDay()];
  if (!$('#fixed').is(':checked')){
    if(timeDiff <= 0){
      $('.note').css('background-color','#F7E650');
      $('.note').css('color','#212121');
      $('.note-warning').html('NOTE: Duration is set to 0');
    }else{
      $('.note').css('background-color','#0277BD');
      $('.note').css('color','white');
      $('.note-warning').html('');
    }
  }else{
    if(duration_days <= 0 && duration_hours <= 0 && duration_minutes <= 0){
      $('.note').css('background-color','#F7E650');
      $('.note').css('color','#212121');
      $('.note-warning').html('NOTE: Duration is set to 0');
    }else if(timeDiff <= 0){
      $('.note').css('background-color','#F7E650');
      $('.note').css('color','#212121');
      $('.note-warning').html('NOTE: Invalid Start between');
    }else{
      $('.note').css('background-color','#0277BD');
      $('.note').css('color','white');
      $('.note-warning').html('');
    }
  }
  var recurrence = $('#recurrence').val();
  if(recurrence == 'custom'){
    var ends_on =$ ('input[name="ends"]:checked').val();
    if(ends_on == 'finite_ends'){
      var ends_on_date =$ ('#endson-date').val();
      var ends_on_str = ' until ' + ends_on_date;
    }else{
      var ends_on_str = '';
    }
    var repeat_every_no = parseInt($('input[name="recurrence_no"]').val());
    var repeat_every_text =$ ('select[name="recurrence_text"]').val();
    if(repeat_every_text == 'day'){
      if(repeat_every_no == 1){
        var repeat_every_str = "daily";
      }else{
        var repeat_every_str = "every " + repeat_every_no + " days";
      }
      var next_start_time = start_time;
      var next_end_time = end_time;
      var next_start_date = new Date(startDate);
      var next_end_date = new Date(endDate);
      next_start_date.setDate(startDate.getDate() + (1 * repeat_every_no));
      next_end_date.setDate(endDate.getDate() + ( 1 * repeat_every_no));
      var clean_next_start_date = next_start_date.getFullYear() + "-" + format_time(next_start_date.getMonth()+1) + "-" + format_time(next_start_date.getDate());
      var clean_next_end_date = next_end_date.getFullYear() + "-" + format_time(next_end_date.getMonth()+1) + "-" + format_time(next_end_date.getDate());
    }
    if(repeat_every_text == "week"){
      var all_selected_day = '';
      var i = 0;
      $('input[name="week_on_day[]"]:checked').each(function(){
        if($(this).is(':checked')){
          var wno = $(this).attr("wno");
          var day = $('span[wno='+wno+']').attr("tag");
          if(i == 0){
            all_selected_day += day;
          }else{
            var next_i = i+1;
            if($('input[name="week_on_day[]"]:checked')[next_i]){
              all_selected_day += ', ';
              all_selected_day += day;
            }else{
              all_selected_day += ' and ';
              all_selected_day += day;
            }
        }
        i += 1;
      }
    });
      if(repeat_every_no == 1){
        var repeat_every_str = "weekly on " + all_selected_day;
      }else{
        var repeat_every_str = "every " + repeat_every_no + " week on " + all_selected_day;
      }
      var next_start_time = start_time;
      var next_end_time = end_time;
      var next_start_date = new Date(startDate);
      var next_end_date = new Date(endDate);
      next_start_date.setDate(startDate.getDate() + (7 * repeat_every_no));
      next_end_date.setDate(endDate.getDate() + (7 * repeat_every_no));
      var clean_next_start_date = next_start_date.getFullYear() + "-" + format_time(next_start_date.getMonth()+1) + "-" + format_time(next_start_date.getDate());
      var clean_next_end_date = next_end_date.getFullYear() + "-" + format_time(next_end_date.getMonth()+1) + "-" + format_time(next_end_date.getDate());
    }
    if(repeat_every_text == "month"){
      var month_on = JSON.parse($('input[name="month_on"]:checked').val());
      var val_day_no = month_on['day_no'];
      var val_day = month_on['day'];
      val_day_no = (val_day_no == "last")? val_day_no : format_date(val_day_no);
      if(val_day_no == "last" && val_day == "last"){
        if(repeat_every_no == 1){
          var repeat_every_str = "monthly on the last day";
        }else{
          var repeat_every_str = "every " + repeat_every_no + " months on the last day";
        }
        var next_start_time = start_time;
        var next_end_time = end_time;
        var next_start_date = new Date(startDate.getFullYear(),startDate.getMonth()+(repeat_every_no+1),1);
        var next_end_date = new Date(endDate);
        next_start_date.setDate(next_start_date.getDate()-1);
        next_end_date.setMonth(endDate.getMonth() + (repeat_every_no));
        var clean_next_start_date = next_start_date.getFullYear() + "-" + format_time(next_start_date.getMonth()+1) + "-" + format_time(next_start_date.getDate());
        var clean_next_end_date = next_end_date.getFullYear() + "-" + format_time(next_end_date.getMonth()+1) + "-" + format_time(next_end_date.getDate());
      }else{
        if(repeat_every_no == 1){
          var repeat_every_str = "monthly on the " + val_day_no + ' '+ day_name;
        }else{
          var repeat_every_str = "every " + repeat_every_no + " months on the " + val_day_no + ' '+ day_name;
        }
        if(val_day_no == "last"){
          var next_start_time = start_time;
          var next_end_time = end_time;
          var last_day_next_month = new Date(startDate.getFullYear(),startDate.getMonth() + (repeat_every_no + 1),0);
          if(last_day_next_month.getDay() >= val_day){
            var diff = last_day_next_month.getDay()-val_day;
            var next_start_date = new Date(startDate.getFullYear(),startDate.getMonth() + (repeat_every_no + 1),0);
            next_start_date.setDate(next_start_date.getDate()-diff);
          }else{
            var diff = val_day-last_day_next_month.getDay();
            var next_start_date = new Date(startDate.getFullYear(),startDate.getMonth() + (repeat_every_no + 1),0);
            next_start_date.setDate(next_start_date.getDate()-7);
            next_start_date.setDate(next_start_date.getDate()+diff);
          }
          var next_end_date = new Date(next_start_date);
          next_end_date.setSeconds(next_end_date.getSeconds() + ((endDate-startDate)/1000));
          var clean_next_start_date = next_start_date.getFullYear() + "-" + format_time(next_start_date.getMonth()+1) + "-" + format_time(next_start_date.getDate());
          var clean_next_end_date = next_end_date.getFullYear() + "-" + format_time(next_end_date.getMonth()+1) + "-" + format_time(next_end_date.getDate());
        }else{
          val_day_no = parseInt(val_day_no);
          var next_start_time = start_time;
          var next_end_time = end_time;
          var first_day_next_month = new Date(startDate.getFullYear(),startDate.getMonth() + (repeat_every_no),1);
          if(first_day_next_month.getDay() <= val_day){
            var diff = val_day-first_day_next_month.getDay();
            var next_start_date = new Date(startDate.getFullYear(),startDate.getMonth() + (repeat_every_no),1);
            next_start_date.setDate(next_start_date.getDate()+diff+(7*(val_day_no-1)));
            if(next_start_date.getMonth() > (startDate.getMonth()+1)){
              var next_start_date = new Date(startDate.getFullYear(),startDate.getMonth() + (repeat_every_no),1);
              next_start_date.setDate(next_start_date.getDate()+diff+(7*(val_day_no-2)));
            }
          }else{
            var diff = first_day_next_month.getDay()-val_day;
            var next_start_date = new Date(startDate.getFullYear(),startDate.getMonth() + (repeat_every_no),1);
            next_start_date.setDate(next_start_date.getDate()+7);
            next_start_date.setDate(next_start_date.getDate()-diff+(7*(val_day_no-1)));
            if(next_start_date.getMonth() > (startDate.getMonth()+1)){
              var next_start_date = new Date(startDate.getFullYear(),startDate.getMonth() + (repeat_every_no),1);
              next_start_date.setDate(next_start_date.getDate()+7);
              next_start_date.setDate(next_start_date.getDate()-diff+(7*(val_day_no-2)));
            }
          }
          var next_end_date = new Date(next_start_date);
          next_end_date.setSeconds(next_end_date.getSeconds()+((endDate-startDate)/1000));
          var clean_next_start_date = next_start_date.getFullYear() + "-" + format_time(next_start_date.getMonth()+1) + "-" + format_time(next_start_date.getDate());
          var clean_next_end_date = next_end_date.getFullYear() + "-" + format_time(next_end_date.getMonth()+1) + "-" + format_time(next_end_date.getDate());
        }
      }
    }
    if(repeat_every_text == "year"){
      var year_on = JSON.parse($('input[name="year_on"]:checked').val());
      var val_day_no = year_on['day_no'];
      var val_day = year_on['day'];
      var val_month = year_on['month'];
      val_day_no = (val_day_no == "last")? val_day_no : format_date(val_day_no);
      var repeat_every_str = "yearly on the " + val_day_no + ' ' + day_name + ' of ' + month_name[val_month];
      if(repeat_every_no == 1){
        var repeat_every_str = "yearly on the " + val_day_no + ' ' + day_name + ' of ' + month_name[val_month];
      }else{
        var repeat_every_str = "every " + repeat_every_no + " years on the " + val_day_no + ' ' + day_name + ' of ' + month_name[val_month];
      }
      if(val_day_no == "last"){
        var next_start_time = start_time;
        var next_end_time = end_time;
        var last_day_next_month = new Date(startDate.getFullYear()+(repeat_every_no),startDate.getMonth()+1,0);
        if(last_day_next_month.getDay() >= val_day){
          var diff = last_day_next_month.getDay()-val_day;
          var next_start_date = new Date(startDate.getFullYear()+(repeat_every_no),startDate.getMonth()+1,0);
          next_start_date.setDate(next_start_date.getDate()-diff);
        }else{
          var diff = val_day-last_day_next_month.getDay();
          var next_start_date = new Date(startDate.getFullYear()+(repeat_every_no),startDate.getMonth()+1,0);
          next_start_date.setDate(next_start_date.getDate()-7);
          next_start_date.setDate(next_start_date.getDate()+diff);
        }
        var next_end_date = new Date(next_start_date);
        next_end_date.setSeconds(next_end_date.getSeconds()+((endDate-startDate)/1000));
        var clean_next_start_date = next_start_date.getFullYear() + "-" + format_time(next_start_date.getMonth()+1) + "-" + format_time(next_start_date.getDate());
        var clean_next_end_date = next_end_date.getFullYear() + "-" + format_time(next_end_date.getMonth()+1) + "-" + format_time(next_end_date.getDate());
      }else{
        val_day_no = parseInt(val_day_no);
        var next_start_time = start_time;
        var next_end_time = end_time;
        var first_day_next_month = new Date(startDate.getFullYear() + repeat_every_no,startDate.getMonth(),1);
        if(first_day_next_month.getDay() <= val_day){
          var diff = val_day-first_day_next_month.getDay();
          var next_start_date = new Date(startDate.getFullYear() + repeat_every_no,startDate.getMonth(),1);
          next_start_date.setDate(next_start_date.getDate()+diff+(7*(val_day_no-1)));
          if(next_start_date.getMonth() > (startDate.getMonth())){
            var next_start_date = new Date(startDate.getFullYear() + repeat_every_no,startDate.getMonth(),1);
            next_start_date.setDate(next_start_date.getDate()+diff+(7*(val_day_no-2)));
          }
        }else{
          var diff = first_day_next_month.getDay()-val_day;
          var next_start_date = new Date(startDate.getFullYear() + repeat_every_no,startDate.getMonth(),1);
          next_start_date.setDate(next_start_date.getDate()+7);
          next_start_date.setDate(next_start_date.getDate()-diff+(7*(val_day_no-1)));
          if(next_start_date.getMonth() > (startDate.getMonth())){
            var next_start_date = new Date(startDate.getFullYear() + repeat_every_no,startDate.getMonth(),1);
            next_start_date.setDate(next_start_date.getDate()+7);
            next_start_date.setDate(next_start_date.getDate()-diff+(7*(val_day_no-2)));
          }
        }
        var next_end_date = new Date(next_start_date);
        next_end_date.setSeconds(next_end_date.getSeconds()+((endDate-startDate)/1000));
        var clean_next_start_date = next_start_date.getFullYear() + "-" + format_time(next_start_date.getMonth()+1) + "-" + format_time(next_start_date.getDate());
        var clean_next_end_date = next_end_date.getFullYear()+ "-" + format_time(next_end_date.getMonth()+1) + "-" + format_time(next_end_date.getDate());
      }
    }
  }else{
    if(recurrence != "no"){
      var ends_on_str = '';
      var repeat_every_str = '';
      var repeat = JSON.parse(recurrence);
      var repeat_every_no = repeat['recur']['no'];
      var repeat_every_text = repeat['recur']['text'];
      if(repeat_every_text == 'day'){
        var repeat_every_no = 1;
        if(repeat_every_no == 1){
          var repeat_every_str = "daily";
        }else{
          var repeat_every_str = "every " + repeat_every_no + " days";
        }
        var next_start_time = start_time;
        var next_end_time = end_time;
        var next_start_date = new Date(startDate);
        var next_end_date = new Date(endDate);
        next_start_date.setDate(startDate.getDate() + (1 * repeat_every_no));
        next_end_date.setDate(endDate.getDate() + ( 1 * repeat_every_no));
        var clean_next_start_date = next_start_date.getFullYear() + "-" + format_time(next_start_date.getMonth()+1) + "-" + format_time(next_start_date.getDate());
        var clean_next_end_date = next_end_date.getFullYear() + "-" + format_time(next_end_date.getMonth()+1) + "-" + format_time(next_end_date.getDate());
      }
      if(repeat_every_text == "week"){
        var repeat_every_no = 1;
        if(repeat_every_no == 1){
          var repeat_every_str = "weekly on " + day_name;
        }else{
          var repeat_every_str = "every " + repeat_every_no + " week on " + day_name;
        }
        var next_start_time = start_time;
        var next_end_time = end_time;
        var next_start_date = new Date(startDate);
        var next_end_date = new Date(endDate);
        next_start_date.setDate(startDate.getDate() + (7 * repeat_every_no));
        next_end_date.setDate(endDate.getDate() + (7 * repeat_every_no));
        var clean_next_start_date = next_start_date.getFullYear() + "-" + format_time(next_start_date.getMonth()+1) + "-" + format_time(next_start_date.getDate());
        var clean_next_end_date = next_end_date.getFullYear() + "-" + format_time(next_end_date.getMonth()+1) + "-" + format_time(next_end_date.getDate());
      }
      if(repeat_every_text == "month"){
        var month_on = repeat['on'];
        var val_day_no = month_on['day_no'];
        var val_day = month_on['day'];
        val_day_no = (val_day_no == "last")? val_day_no : format_date(val_day_no);
        var repeat_every_no = 1;
        if(val_day_no == "last" && val_day == "last"){
          if(repeat_every_no == 1){
            var repeat_every_str = "monthly on the last day";
          }else{
            var repeat_every_str = "every " + repeat_every_no + " months on the last day";
          }
          var next_start_time = start_time;
          var next_end_time = end_time;
          var next_start_date = new Date(startDate.getFullYear(),startDate.getMonth()+(repeat_every_no+1),1);
          var next_end_date = new Date(endDate);
          next_start_date.setDate(next_start_date.getDate()-1);
          next_end_date.setMonth(endDate.getMonth()+(repeat_every_no));
          var clean_next_start_date = next_start_date.getFullYear() + "-" + format_time(next_start_date.getMonth()+1) + "-" + format_time(next_start_date.getDate());
          var clean_next_end_date = next_end_date.getFullYear() + "-" + format_time(next_end_date.getMonth()+1) + "-" + format_time(next_end_date.getDate());
        }else{
          var repeat_every_str = "monthly on the " + val_day_no + ' ' + day_name;
          val_day_no = parseInt(val_day_no);
          var next_start_time = start_time;
          var next_end_time = end_time;
          var first_day_next_month = new Date(startDate.getFullYear(),startDate.getMonth() + (repeat_every_no),1);
          if(first_day_next_month.getDay() <= val_day){
            var diff = val_day-first_day_next_month.getDay();
            var next_start_date = new Date(startDate.getFullYear(),startDate.getMonth() + (repeat_every_no),1);
            next_start_date.setDate(next_start_date.getDate()+diff+(7*(val_day_no-1)));
            if(next_start_date.getMonth() > (startDate.getMonth()+1)){
              var next_start_date = new Date(startDate.getFullYear(),startDate.getMonth()+(repeat_every_no),1);
              next_start_date.setDate(next_start_date.getDate()+diff+(7*(val_day_no-2)));
            }
          }else{
            var diff = first_day_next_month.getDay()-val_day;
            var next_start_date = new Date(startDate.getFullYear(),startDate.getMonth()+(repeat_every_no),1);
            next_start_date.setDate(next_start_date.getDate()+7);
            next_start_date.setDate(next_start_date.getDate()-diff+(7*(val_day_no-1)));
            if(next_start_date.getMonth() > (startDate.getMonth()+1)){
              var next_start_date = new Date(startDate.getFullYear(),startDate.getMonth()+(repeat_every_no),1);
              next_start_date.setDate(next_start_date.getDate()+7);
              next_start_date.setDate(next_start_date.getDate()-diff+(7*(val_day_no-2)));
            }
          }
          var next_end_date = new Date(next_start_date);
          next_end_date.setSeconds(next_end_date.getSeconds()+((endDate-startDate)/1000));
          var clean_next_start_date = next_start_date.getFullYear()+"-"+ format_time(next_start_date.getMonth()+1) +"-"+ format_time(next_start_date.getDate());
          var clean_next_end_date = next_end_date.getFullYear()+"-"+ format_time(next_end_date.getMonth()+1) +"-"+ format_time(next_end_date.getDate());
        }
      }
    }
  }
  if(recurrence == 'no'){
    $('#schedule-notification').show();
    $('#recur-note').html('No recurrence');
    if(flexible){
      if(start_date == end_date){
        $('#duration-note').html('Schedule downtime occurs for ' + f_duration_string + '  with flexible starttime between ' + start_time + '  to ' + end_time + ' on ' + start_date);
      }else{
        $('#duration-note').html('Schedule downtime occurs for ' + f_duration_string + '  with flexible starttime between ' + start_time + ' on ' + start_date + '  to ' + end_time + ' on ' + end_date);
      }
    }else{
      if(start_date == end_date){
        $('#duration-note').html('Schedule downtime occurs ' + start_time + '  to ' + end_time + ' on ' + start_date);
      }else{
        $('#duration-note').html('Schedule downtime occurs ' + start_time + ' on ' + start_date + '  to ' + end_time + ' on ' + end_date);
      }
    }
  }else{
    $('#recur-note').html('');
    if(flexible){
      if(start_date == end_date){
        $('#duration-note').html('Repeat ' + repeat_every_str + '' + ends_on_str + '. First scheduled downtime occurs for ' + f_duration_string + '  with flexible starttime between ' + start_time + ' to ' + end_time + ' on ' + start_date + '. Next occurs between ' + next_start_time + ' to ' + next_end_time + ' on ' + clean_next_start_date + '. ');
      }else{
        $('#duration-note').html('Repeat ' + repeat_every_str + '' + ends_on_str + '. First scheduled downtime occurs for ' + f_duration_string + '  with flexible starttime between ' + start_time + ' on ' + start_date + ' to ' + end_time + ' on ' + end_date + '. Next occurs between ' + next_start_time + ' on ' + clean_next_start_date + ' to ' + next_end_time + ' on ' + clean_next_end_date + '.');
      }
    }else{
      if(start_date == end_date){
        $('#duration-note').html('Repeat ' + repeat_every_str + '' + ends_on_str + '. First scheduled downtime occurs ' + start_time + ' to ' + end_time + ' on ' + start_date + '. Next occurs ' + next_start_time + ' to ' + next_end_time + ' on ' + clean_next_start_date + '. ');
      }else{
        $('#duration-note').html('Repeat ' + repeat_every_str + '' + ends_on_str + '. First scheduled downtime occurs ' + start_time + ' on ' + start_date + '  to ' + end_time + ' on ' + end_date + '. Next occurs ' + next_start_time + ' on ' + clean_next_start_date +'  to ' + next_end_time + ' on ' + clean_next_end_date + '. ');
      }
    }
    if(recurrence = "custom" && $('#exclude_date_block div.active input').val() != ''){
     dates = ' ';
     var exclude_input = [];
     var i = 0;
     $('#exclude_date_block div.active input').each(function(){
       thisid = $(this).attr('excludeId');
       arr = thisid.split('-');
       if(arr[2] == "start"){
        exclude_input[i] = arr[3];
        i += 1;
      }
    })
     var j = 0;
     $('#exclude_date_block div.active input').each(function(){
       thisid = $(this).attr('excludeId');
       arr = thisid.split('-');
       if(arr[2] == "start" && $('input[excludeId="exclude-date-start-'+arr[3]+'"]').val() != ''){
         dates += $('input[excludeId="exclude-date-start-'+arr[3]+'"]').val();
         if($('input[excludeId="exclude-date-end-'+arr[3]+'"]').val() != '' ){
          dates += ' to ';
          dates += $('input[excludeId="exclude-date-end-'+arr[3]+'"]').val();

        }
        dates_all = dates+',';
        var next_j = j+1;
        var next_id = exclude_input[next_j];
        if($('input[excludeId="exclude-date-start-'+next_id+'"]').length != 0 && $('input[excludeId="exclude-date-start-'+next_id+'"]').val() != ''){
          dates += ', ';
        }else{
          dates += '.';
        }
        j += 1;
      }
    })
     $('#exclude_days_all').val(dates_all);
     var exclude_note = "Periods excluded from the schedule:" + dates;
     $('#exclude-note').html(exclude_note).css("display","block");
   }else{
    $('#exclude-note').html('').hide();
  }
}
}

function quickselect_data(time,pre){
  time = '00:00';
  time = time.split(":");
  var data = '';
  var hour = parseInt(time[0]);
  var min = (parseInt(time[1])<30)? 00 : 30;
  var start = hour+1;
  var start_i = false;
  for(i = start; i<24; i++){
    if(!min && !start_i){
      data += '<div id="'+pre+''+format_hour(hour)+':30" class="time">'+format_hour(hour)+':30</div>';
      start_i = true;
    }
    data += '<div id="'+pre+''+format_hour(i)+':00" class="time">'+format_hour(i)+':00</div>';
    data += '<div id="'+pre+''+format_hour(i)+':30" class="time">'+format_hour(i)+':30</div>';
  }
  for(i = 0; i < start; i++){
    data += '<div id="'+pre+''+format_hour(i)+':00" class="time">'+format_hour(i)+':00</div>';
    data += '<div id="'+pre+''+format_hour(i)+':30" class="time">'+format_hour(i)+':30</div>';
  }
  return data;
}


function format_hour(hour){
  if(hour < 10){
    return '0' + hour;
  }else{
    return hour;
  }
}

function endtime_quickselect_data(time){
  var data = '';
  for(i = 0; i < 24; i++){
    if(i < 10){
      data += '<div class="time">0'+i+':30</div>';
      data += '<div class="time">0'+i+':30</div>'
    }else{
      data += '<div class="time">'+i+':30</div>';
      data += '<div class="time">'+i+':30</div>';
    }
  }
  return data;
}

function format_time(time) {
  if(time < 10){
    return '0'+time;
  }else{
    return time;
  }
}

function format_date(date){
  var suffix = '';
  switch(date) {
   case 1: case 21: case 31: suffix = 'st'; break;
   case 2: case 22: suffix = 'nd'; break;
   case 3: case 23: suffix = 'rd'; break;
   default: suffix = 'th';
 }
 return date+suffix;
}

function format_num_word(no){
  switch(no) {
    case 1: return "first"; break;
    case 2: return "second"; break;
    case 3: return "third"; break;
    case 4: return "fourth"; break;
    default: return "last";
  }
}

function check_timestring(timestring) {
  if (timestring.indexOf(':') === -1) {
    return false;
  }
  // We have hh:mm or hh:mm:ss
  var timeparts = timestring.split(':');
  if ((timeparts.length !== 2 && timeparts.length !== 3) ||
    isNaN(timeparts[0]) ||
    isNaN(timeparts[1]) ||
    (timeparts.length === 3 && isNaN(timeparts[2]))
    ) {
    return false;
}
return true;
}


function check_timestring_duration(timestring) {
     // We have 00d 00h 00m 00s
     var timeparts = timestring.split(' ');
     if (timeparts.length !== 4
      || timeparts[0].substr(timeparts[0].length - 1) !== 'd'
      || timeparts[1].substr(timeparts[1].length - 1) !== 'h'
      || timeparts[2].substr(timeparts[2].length - 1) !== 'm'
      || timeparts[1].slice(0, -1) > 23
      || timeparts[2].slice(0, -1) > 59
      || timeparts[3].slice(0, -1) > 59
      ) {
       return false;
   }
   return true;
 }

 function duration_conversion(days, hours, minutes) {
  duration = (parseInt(days*24)+parseInt(hours))+":"+minutes+":00";
  return duration;
}

function check_setup() {
  var errors = [];
  var comment = op5trim($('textarea[name=comment]').val());
  var obj_count=$('#objects option').length;
  var start_time = op5trim($('input[name=start_time]').val());
  var end_time = op5trim($('input[name=end_time]').val());
  var fixed = $('#fixed').attr('checked');
  var start_date = op5trim($('#fixed-duration-start-date').val());
  var end_date = op5trim($('#fixed-duration-end-date').val());
  var exclude_days = $('#exclude_days_all').val();
  var duration_days = $('#duration-days').val();
  var duration_hours = $('#duration-hours').val();
  var duration_minutes = $('#duration-minutes').val();
  var duration_new = duration_conversion(duration_days, duration_hours, duration_minutes);
  if (comment == '' || start_time == '' || end_time == '' || (fixed == '') || obj_count == 0) {
    // required fields are empty
    // _form_err_empty_fields
    errors.push(_form_err_empty_fields);
  } else {
    // check for special input
    // start_time field
    if (!check_timestring(start_time)) {
      errors.push(_form_err_bad_timeformat.replace('{field}', _form_field_start_time));
    }
    // end_time field
    if (!check_timestring(end_time)) {
      errors.push(_form_err_bad_timeformat.replace('{field}', _form_field_end_time));
    }
    var startDate = new Date(start_date+"T"+start_time);
    var endDate = new Date(end_date+"T"+end_time);
    if(startDate.getTime() >= endDate.getTime()){
      errors.push(_form_err_empty_fields);
    }
  }
  if (errors.length) {
    $.notify(errors.join(", "), {sticky: true, type: 'warning'});
    return false;
  }
  /**
   * Everything validated ok.
   * Check if schedule matches today and if so ask the user if a downtime
   * should be inserted today.
   */
   if (fixed) {
    fixed = 0;
  } else {
    fixed = 1;
  }
  var d = new Date();
  var startDate = new Date(start_date+"T"+start_time);
  if (startDate.getYear() == d.getYear() && startDate.getMonth() == d.getMonth() && startDate.getDate() == d.getDate()) {
    if (confirm("The schedule you are creating matches today, would you like to schedule a downtime for today?\nClick 'Cancel' to save your recurring schedule without scheduling a downtime for today or 'Ok' to save recurring schedule and schedule downtimes today.")) {
      // Downtime type string
      var object_type = $('#report_type option:selected').val();
      // Array of selected objects
      var objects = [];
      $('#objects option').each(function() {
        objects.push($(this).val());
      });

      $.ajax({
        url: _site_domain + _index_page + '/recurring_downtime/insert_downtimes',
        type: 'post',
        async: false,
        data: {
          objects: objects,
          object_type: object_type,
          start_time: start_time,
          end_time: end_time,
          start_date: start_date,
          end_date: end_date,
          fixed: fixed,
          duration: duration_new,
          comment: comment,
          exclude_days: exclude_days,
          csrf_token: _csrf_token
        },
        success: function(result) {
         $.notify(result);
       },
       error: function(result) {
         $.notify(result.responseText, {'sticky':true, 'type':'critical'});
       }
     });
    }
  }
  return true;
}
