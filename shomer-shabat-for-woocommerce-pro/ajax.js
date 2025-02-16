jQuery(document).ready(function($){

  // Set initial method
  let active_method = $('#range_form .label_wrap').attr("data-method");
  if (active_method) {
    switch (active_method) {
      case 'calendar':
      $(".range").removeClass("week");
      $(".range").removeClass("shabbat");
      $('#range_form .calendar_method input').prop('required',true);

        $(".range").addClass("calendar");
        break;
      case 'week':
      $(".range").removeClass("calendar");
      $(".range").removeClass("shabbat");
      $('#range_form .week_method input').prop('required',true);
        $(".range").addClass("week");
        break;
        case 'shabbat':
        $(".range").removeClass("calendar");
        $('#range_form .shabbat_method input').prop('required',true);
        $(".range").removeClass("week");
        $(".range").addClass("shabbat");
          break;

    }
    $('#range_form input[type=radio][name=method]').each(function() {
      if ($(this).val() == active_method) {
          $(this).prop('checked', true).attr('checked', 'checked');
      }
      else {
          $(this).prop('checked', false).removeAttr('checked');
      }
    });
  }

  // Choose method
  $('#range_form input[type=radio][name=method]').on('change', function() {
  switch ($(this).val()) {
    case 'calendar':
    $(".range").removeClass("week");
      $(".range").addClass("calendar");
      $(".range").removeClass("shabbat");
      $('#range_form input[type=radio][name=method]').prop('checked', false).removeAttr('checked');
      $('#range_form .calendar_method input').prop('required',true);
      $('#range_form .week_method input').removeAttr('required');
      $('#range_form .shabbat_method input').removeAttr('required');
      $(this).prop('checked', true).attr('checked', 'checked');
      break;
    case 'week':
    $(".range").removeClass("calendar");
    $(".range").removeClass("shabbat");
      $(".range").addClass("week");
      $('#range_form input[type=radio][name=method]').prop('checked', false).removeAttr('checked');
      $('#range_form .week_method input').prop('required',true);
      $('#range_form .calendar_method input').removeAttr('required');
      $('#range_form .shabbat_method input').removeAttr('required');
      $(this).prop('checked', true).attr('checked', 'checked');
      break;
      case 'shabbat':
      $(".range").removeClass("calendar");
        $(".range").removeClass("week");
        $(".range").addClass("shabbat");
        $('#range_form input[type=radio][name=method]').prop('checked', false).removeAttr('checked');
        $('#range_form .shabbat_method input').prop('required',true);
        $('#range_form .week_method input').removeAttr('required');
        $('#range_form .calendar_method input').removeAttr('required');
        $(this).prop('checked', true).attr('checked', 'checked');
        break;
  }
});

  // Set min or max date for calendar range on change
  $(".calendar_wrap").each(function( i ) {
    $($('.start_date')[i]).on("change", function(){
       $($('.finish_date')[i]).prop("min", $(this).val());
    });
    $($('.finish_date')[i]).on("change", function(){
       $($('.start_date')[i]).prop("max", $(this).val());
    });
  });

   $( "#range_form" ).on( "submit", function (e) {
     e.preventDefault();
     function timeToHours(time) {
       time = time.split(/:/);
       return time[0];
     }
     function timeToMinutes(time) {
       time = time.split(/:/);
       return time[1];
     }

     let all_data = [];
     let active_method = $("#range_form input[name='method']:checked").val();
     let from_offset = $("#range_form input[name='from_offset']").val();
     let to_offset = $("#range_form input[name='to_offset']").val();

     $(".range_wrap").each(function( i ) {
       let id = $(this).attr('data-row_id');
       let from_day = $($('.from_day')[i]).val();
       let from_time = $($('.from_time')[i]).val();
       let to_day = $($('.to_day')[i]).val();
       let to_time = $($('.to_time')[i]).val();
       let phrase = $(".phrase").val();

       let from_hours = timeToHours(from_time);
       let from_minutes = timeToMinutes(from_time);
       let to_hours = timeToHours(to_time);
       let to_minutes = timeToMinutes(to_time);

       all_data.push({
         'id': id,
         'from_day': from_day,
         'from_hours': from_hours,
         'from_minutes': from_minutes,
         'to_day': to_day,
         'to_hours': to_hours,
         'to_minutes': to_minutes,
         'phrase': phrase,
         'from_offset': from_offset,
         'to_offset': to_offset
       })

     });

     let calendar_data = [];

     $(".calendar_wrap").each(function( i ) {
       let id = $(this).attr('data-row_id');
       let start_date = $($('.start_date')[i]).val();
       let finish_date = $($('.finish_date')[i]).val();

       calendar_data.push({
         'id': id,
         'active_method': active_method,
         'start_date': start_date,
         'finish_date': finish_date,
       })

     });

     var data = {
         'action': 'handle_request',
         'all_data': all_data,
         'calendar_data': calendar_data
     };

     jQuery.ajax({
     url : ajax_script.ajaxurl,
     type : 'post',
     data : data,
     success : function(){
       $('.wp-core-ui .notice.is-dismissible.disable_success').addClass("active");
       $('.wp-core-ui .notice.is-dismissible.disable_success button').on("click", function(){$('.wp-core-ui .notice.is-dismissible.disable_success').removeClass("active");})
     }
   });

     return false;
   });

    // Initial method

   // Initial day of week

   if ($("#range_form")) {
     for (var i = 0; i < $('.from_day').length; i++) {
     const from_day = $($('.from_day')[i]).attr("data-selected");
     const to_day = $($('.to_day')[i]).attr("data-selected");


     if(from_day){
       $($('.from_day')[i]).children().each(function(){
         if ($(this).val() == from_day) {
           $(this).attr("selected", true)
         }
     });
     }

     if(to_day){
       $($('.to_day')[i]).children().each(function(){
         if ($(this).val() == to_day) {
           $(this).attr("selected", true)
         }
     });
     }

   }
 }


 function incert_new_range(){
   $(".add_new_range").parent().before('<div class="range_wrap"><div><div>From:<div><select class="from_day" name="from_day[]" data-selected=""><option value="0">Sunday</option><option value="1">Monday</option><option value="2">Tuesday</option><option value="3">Wednesday</option><option value="4">Thursday</option><option value="5">Friday</option><option value="6">Saturday</option></select><input type="time" class="from_time" name="from_time[]" required ></div></div> <div>To:<div><select class="to_day" name="to_day[]" data-selected=""><option value="0">Sunday</option><option value="1">Monday</option><option value="2">Tuesday</option><option value="3">Wednesday</option><option value="4">Thursday</option><option value="5">Friday</option><option value="6">Saturday</option></select><input type="time" class="to_time" name="to_time[]" required></div></div> </div> <div><a href="#" class="delete_range" ><span class="dashicons dashicons-trash"></span></a></div></div>');
   $(".delete_range").on("click", function(e){
     $(this).addClass("clicked")
     e.preventDefault();
     let row_id = $(this).attr('data-row_id');
     $(".range_wrap").each(function( index ) {
       let is_this_row = $(this).attr('data-row_id') == row_id;
       if (is_this_row) {
         if (row_id) {
           jQuery.ajax({
           url : ajax_script.ajaxurl,
           type : 'post',
           data : {
             'action': 'delete_range',
             'row_id': row_id
           }
         });
         $(this).remove();
       }else if($(this).find('.delete_range.clicked').length !== 0){
         $(this).remove();
       }
     }
     });
   });
 }

 $(".add_new_range").on("click", function(e){
   e.preventDefault();
   incert_new_range();
 });
 $(".clear_range").on("click", function(e){
   e.preventDefault();
   $(".first_range option:selected").prop("selected", false);
   $('.first_range .from_time').val('00:00');
   $('.first_range .to_time').val('00:00');
 })


 $(".delete_range").on("click", function(e){
   $(this).addClass("clicked")
   e.preventDefault();
   let row_id = $(this).attr('data-row_id');
   $(".range_wrap").each(function( index ) {
     let is_this_row = $(this).attr('data-row_id') == row_id;
     if (is_this_row) {
       if (row_id) {
         jQuery.ajax({
         url : ajax_script.ajaxurl,
         type : 'post',
         data : {
           'action': 'delete_range',
           'row_id': row_id
         }
       });
       $(this).remove();
     }else if($(this).find('.delete_range.clicked').length !== 0){
       $(this).remove();
     }
   }
   });
 });



});
