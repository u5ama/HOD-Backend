//////////////////////////////////////////////
//////////////USER INFO///////////////////////
//////////////////////////////////////////////
/*$(document.body).on('click', '.next-div' ,function() {
    $(".first-div").css("display", "none");
    $(".second-div").css("display", "block");
    $('#second').addClass('active');
    $('#first').removeClass('active');

});
$(document.body).on('click', '.user-next' ,function() {
    $(".second-div").css("display", "none");
    $(".third-div").css("display", "block");
    $('#second').removeClass('active');
    $('#third').addClass('active');
});*/

//////////////////////////////////////////////
//////////////USER INFO///////////////////////
//////////////////////////////////////////////
//////////////////////////////////////////////
//////////////////////////////////////////////
// $(document.body).on('click', '.oct-relative' ,function() {
//   var value = $(this).closest('.oct-relative').text();
//   if(value === "Existing User"){
//     $(".new-user-personal-detail-area").css("display", "none");
//     $(".existing-user-login").css("display", "block");
//     $(".guest-user-personal-detail-area").css("display", "none");
//     $(".user-next").css("display", "none");
//   }else if(value === "New User"){
//     $(".new-user-personal-detail-area").css("display", "block");
//     $(".existing-user-login").css("display", "none");
//     $(".guest-user-personal-detail-area").css("display", "none");
//     $(".user-next").css("display", "block");
//   }else if(value === "Guest User"){
//     $(".new-user-personal-detail-area").css("display", "none");
//     $(".existing-user-login").css("display", "none");
//     $(".guest-user-personal-detail-area").css("display", "block");
//     $(".user-next").css("display", "block");
//   }
//
// });

//////////////////////////////////////////////
//////////////////////////////////////////////

$('.oct-value1').click(function(e) {
    e.preventDefault();
    var value = $(this).closest('.oct-value1').data('value'); // = 9
    $('.change-value').text(value);
});

$(document.body).on('click', '.oct-value2' ,function(e) {
    e.preventDefault();
    var value = $(this).closest('.oct-value2').data('value'); // = 9
    $('.change-value1').text(value);
});
//////// Open Modal When Clicked on Date
$(document.body).on('click', '.day' ,function() {
    $('#myModal123').modal();
});


$(document).ready(function(){
    $(".location-selection").click(function(){
        $(".location-selection").toggleClass("clicked");
    });
});
$(document).ready(function(){
    $(".service-selection").click(function(){
        $(".service-selection").toggleClass("clicked");
    });
});

//////////////////////////////////////////
//////////////////////////////////////////
////////////Calendar Jquery/////////////
//////////////////////////////////////////
//////////////////////////////////////////
myEvents = [
    { name: "New Year", date: "Wed Jan 01 2020 00:00:00 GMT-0800 (Pacific Standard Time)", type: "holiday", everyYear: true },
    { name: "Valentine's Day", date: "Fri Feb 14 2020 00:00:00 GMT-0800 (Pacific Standard Time)", type: "holiday", everyYear: true },
    { name: "Birthday", date: "February/3/2020", type: "birthday" },
    { name: "Author's Birthday", date: "February/15/2020", type: "birthday", everyYear: true },
    { name: "Holiday", date: "February/15/2020", type: "event" },
],
    $('#evoCalendar').evoCalendar({
        calendarEvents: myEvents
    });
$('#evoCalendar').evoCalendar({
    language: 'en'
});

$('#evoCalendar').evoCalendar({
    todayHighlight:false
});
$('#evoCalendar').evoCalendar({
    sidebarToggler: true,
    sidebarDisplayDefault: true,
    eventListToggler: true,
    eventDisplayDefault: true,
});
$('#evoCalendar').evoCalendar({
    firstDayOfWeek: 0 // Sunday
})
/*$('#evoCalendar').evoCalendar({
    disabledDate: ["02/17/2020", "02/21/2020"]
});*/
$('#evoCalendar').evoCalendar({
    onSelectDate: function() {
        // console.log('onSelectDate!')
    }
});
$('#evoCalendar').evoCalendar({
    theme: 'Midnight Blue'
});

$('#evoCalendar').evoCalendar({
    format:'yyyy-mm-dd',
});

// set theme
$("#evoCalendar").evoCalendar('setTheme', themeName);

// toggle sidebar
$("#evoCalendar").evoCalendar('toggleSidebar', true/false);

// toggle event list
$("#evoCalendar").evoCalendar('toggleEventList', true/false);

// get the selected date
$("#evoCalendar").evoCalendar('getActiveDate');

// get active events
$("#evoCalendar").evoCalendar('getActiveEvents');

// select a year
$("#evoCalendar").evoCalendar('selectYear', yearNumber);

// select a month
$("#evoCalendar").evoCalendar('selectMonth', monthNumber);

// select a date
$("#evoCalendar").evoCalendar('selectDate', dateNumber);

// add events
$("#evoCalendar").evoCalendar('addCalendarEvent', [{
    id: 'Event ID',
    name: "Event Name",
    date: "Date Here",
    type: "Event Type",
    everyYear: true
}]);

// remove events by ID
$("#evoCalendar").evoCalendar('removeCalendarEvent', eventID);

// destroy the calendar
$("#evoCalendar").evoCalendar('destroy');

$('#evoCalendar').evoCalendar({
    // options here
})
    .on('selectDate', function() {
        // do something
    })
    .on('selectEvent', function() {
        // do something
    })
    .on('destroy', function() {
        // do something
    })
//////////////////////////////////////////
//////////////////////////////////////////
////////////Calendar Jquery/////////////
//////////////////////////////////////////
//////////////////////////////////////////
