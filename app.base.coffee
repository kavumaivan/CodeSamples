app = angular.module 'smartcal.app.base', ['smartcal.api','smartcal.util','ui.calendar','angularSpinner','ngSanitize','ui.bootstrap','ngAnimate','mymodal']

app.controller 'AppController', ['Util','$location', '$anchorScroll', '$filter' ,'$compile' ,'$http','$log','$scope', 'Event', 'UserEvent','Location','Category','Contact', 'AuthUser','User',( Util,$location, $anchorScroll , $filter,$compile, $http , $log , $scope , Event, UserEvent, Location, Category,Contact, AuthUser, User ) ->

    $scope.showModal = false
    $scope.isnewevent = true
    $scope.category_error =  false
    $scope.user = null
    $scope.timezoneOffset =  new Date().getTimezoneOffset()
    $scope.showSpinner = true
    $('[data-toggle="tooltip"]').tooltip()

    User.get({username : AuthUser.username})
    .$promise.then((user) ->
        $scope.user = user
        )

    Category.query().$promise.then (results) ->
      $scope.categories = results

    $scope.toggleModal = () ->
        $scope.showModal = !$scope.showModal
        return

    #Add event.
    $scope.resetObjects = ()->
        $scope.errors = null
        $scope.event = new Event()
        $scope.event.public = true
        $scope.location = new Location()
        $scope.contact = new Contact()
        $scope.event.category = new Category()

    $scope.resetObjects()
    $scope.canDelete = (event) ->
      if (angular.isUndefined(event) || event == null )
          return false

      if (angular.isDefined(event.user) && angular.isDefined(AuthUser.username))
        return event.user.username == AuthUser.username
      else
        return false

    $scope.DeleteEvent = () ->
        if(confirm('Are you sure you want to delete '+$scope.event.name+'?'))
            #$log.debug("token:" + $scope.csrf_token)
            $http.defaults.headers.common['X-CSRFToken'] = $scope.csrf_token
            Event.delete({ id:$scope.event.id })

            $scope.removeEventsCalendar($scope.currentevents,$scope.event)
            $scope.removeEventsCalendar($scope.events,$scope.event)
            $scope.toggleModal()

    $scope.getTemplate = ( url, callback )->
        $http({
          method: 'GET'
          url: url
        })
        .then(
            (template) ->
              $compile($("#modal_load_element").html(template.data).contents())($scope);
              callback()
            ,(error) ->
            # $log.debug(error)
        )
        return


    $scope.currentEvent = null
    $scope.openCurrentEvent = (listevent)->
        #$log.debug("Eventid = "+listevent.id)
        #$log.debug("window width =>"+window.innerWidth)
        if(window.innerWidth > 765)
            $("#event_detail"+listevent.id).hide
            $scope.currentEvent = listevent
            $location.hash('mainPageHeader');
            $anchorScroll();
        else
            $("#event_detail"+listevent.id).toggle()
            $scope.currentEvent = null

    $scope.formatLocation= (location) ->
        if (location == "no venue yet,,,")
            return ""
        else
            return " "+ location

    $scope.formatPrice = (amount) ->
        tempNum = $filter('number')(amount,2)
        if (tempNum == "0.00")
            return '<span class="price_free">Free</span>'
        else
            return '$' + tempNum ;


    $scope.searchEventsCategory = (eventlist,categoryListStr)->
        if(categoryListStr == "")
            return eventlist

        currentevents = []
        categoryList = categoryListStr.toLowerCase().split(',')
        i = eventlist.length - 1
        j = 0
        while i > 0
            while j < categoryList.length
              #$log.debug(eventlist[i].category.name ," verse " ,categoryList[j])
              if (Util.substr(eventlist[i].category.shortname.toLowerCase(),categoryList[j]) || Util.substr(eventlist[i].category.name.toLowerCase(),categoryList[j]) || Util.substr(eventlist[i].name.toLowerCase(),categoryList[j]))
                  currentevents.push(eventlist[i])
                  break
              j++
            i--
            j = 0
        return currentevents

    $scope.searchEventsDateRange = (array,startDate,endDate)->
        events = []
        i = array.length - 1
        while i > 0
          item = new Date(array[i].start_date)
          if Util.dateToNumber(startDate) < Util.dateToNumber(item) && Util.dateToNumber(item) < Util.dateToNumber(endDate)
              events.push(array[i])
          i--
        return events


    $scope.currentevents = []
    $scope.AfterEventsLoaded = ()->
        $scope.currentevents = []
        $scope.IsFavorateView = false
        firstlast = $scope.getFirstLastDayMonth($scope.currentMonthYear)
        curevents = $scope.searchEventsDateRange($scope.events,firstlast[0],firstlast[1])
        cats = $("#categorynames").val()
        $scope.currentevents = $scope.searchEventsCategory(curevents,cats)
        if $scope.currentevents.length > 0
          $scope.currentEvent = $scope.currentevents[0]
        else
          $scope.currentEvent = null

    $scope.addMinutes = (date, minutes) ->
        return new Date(date.getTime() + minutes*60000)


    $scope.tempevents = []
    $scope.favorateEvents = []

    # Util.addAndSort($scope.currentevents, event)
    $scope.addEventToFavorate = (event)->
        event.start_date = Util.convertUTCDateToLocalDate(event.start_date)
        event.end_date = Util.convertUTCDateToLocalDate(event.end_date)
        Util.addAndSort($scope.favorateEvents, event)

    $scope.IsFavorateView = false
    $scope.showFavIcon = (event)->
        if(!$scope.IsFavorateView && !$scope.canDelete(event))
            return true
        else
            return false


    $scope.AfterEventsFavLoaded = ()->
        $scope.currentevents = []
        firstlast = $scope.getFirstLastDayMonth($scope.currentMonthYear)
        $scope.currentevents = $scope.searchEventsDateRange($scope.favorateEvents,firstlast[0],firstlast[1])
        $scope.showSpinner = false
        $scope.IsFavorateView = true
        if $scope.currentevents.length > 0
          $scope.currentEvent = $scope.currentevents[0]
        else
          $scope.currentEvent = null

    $scope.addEventToCalender = (event)->
        event.start_date = Util.convertUTCDateToLocalDate(event.start_date)
        event.end_date = Util.convertUTCDateToLocalDate(event.end_date)
        Util.addAndSort($scope.events, event)

    #remove the event if it already exists.
    $scope.removeEventsCalendar = (events,event)->
        foundEvent = Util.findByKey(events , 'id' , event.id)
        index = events.indexOf(foundEvent)
        if(index > -1)
          events.splice(index,1)

    #save event function.
    $scope.saveEvent = (event)->
        $scope.showSpinner = true
        #$log.debug("token:" + $scope.csrf_token)
        if (angular.isUndefined($scope.contact.name) || angular.isUndefined($scope.event.name)  || angular.isUndefined($scope.location.address) ||
           angular.isUndefined($scope.location.city) || angular.isUndefined($scope.location.state) || angular.isUndefined($scope.location.zip) )
          $scope.errors = "All the fields with a red boarder are required."
          return

        $http.defaults.headers.common['X-CSRFToken'] = $scope.csrf_token
        event = angular.copy($scope.event)
        event.start_date =  new Date(event.start_date)
        event.end_date =  new Date(event.end_date)

        event.user = angular.copy($scope.user)
        event.location = angular.copy($scope.location)
        $scope.contact.contact_type = "Cell Phone"
        event.contact = angular.copy($scope.contact)

        $scope.category_error =  false
        catId = 1
        if(angular.isDefined($scope.event.category))
          catId  = $scope.event.category

        if (angular.isDefined($scope.event.category.id))
          catId  = $scope.event.category.id

        #$log.debug("category Id =" + angular.toJson($scope.event.category,true))
        #$log.debug("All categories" + angular.toJson($scope.categories,true))
        event.category = Util.findByKey($scope.categories , 'id' , parseInt(catId) )
        if (event.category == null)
          $scope.category_error = true
          return

        #$log.debug("Before Save Event:" + angular.toJson(event, true))
        tosaveEvent = angular.copy(event)
        tosaveEvent.start_date =  Util.convertLocalDateToUTCDate(tosaveEvent.start_date)
        tosaveEvent.end_date =  Util.convertLocalDateToUTCDate(tosaveEvent.end_date)

        if($scope.isnewevent && angular.isUndefined(tosaveEvent.id))
          tosaveEvent.$save()
          .then((eventsaved)->
              #Do nothing dont wait.
          )
          .catch((req)->
              $scope.errors = req.data
              )
        else
          Event.update({ id:tosaveEvent.id },tosaveEvent);
          $scope.removeEventsCalendar($scope.events,event)
          $scope.removeEventsCalendar($scope.currentevents,event)

        Util.addAndSort($scope.events, event)
        $scope.AfterEventsLoaded()
        $scope.currentEvent = event
        $scope.showSpinner = false
        $scope.resetObjects()
        $scope.toggleModal()

    $scope.addFavorate = (event) ->
        $scope.getTemplate('/events/edit',()->
            csrf_token = $('#csrf_token').val()
            $http.defaults.headers.common['X-CSRFToken'] = csrf_token
            $http({  method: 'POST', url:'/api/users/'+ AuthUser.username + '/add_fav/'+ event.id ,data : event })
            .then((results)->
                $log.debug(results)
              )
        )
    $scope.addEvent = ()-> #date, jsEvent, view)->
      $log.debug("Add Event clicked")
      if($scope.user.canAddEvent)
          $scope.getTemplate('/events/edit',()->
            $scope.csrf_token = $('#csrf_token').val()
            Category.query().$promise.then (results) ->
              $scope.categories = results
            $scope.isnewevent = true
            $scope.resetObjects()
            date = new Date()
            #2016-01-18T16:00:00.420000Z  --9:00 PST-1   d.toISOString();
            $scope.event.start_date = Util.myDateTimeFormat(date)
            $scope.event.end_date =  Util.myDateTimeFormat(date)
            $scope.toggleModal()
          )
      else
        $scope.getTemplate('/payment',()->
            $scope.csrf_token = $('#csrf_token').val()
            $scope.toggleModal()
        )
    $scope.Isselected = (id)->
      if( id == $scope.event.category.id)
        return "selected"
    #View Event
    $scope.editEvent = (event) ->
        $scope.getTemplate('/events/edit',()->
          $scope.isnewevent = false
          $scope.csrf_token = $('#csrf_token').val()
          Category.query().$promise.then (results) ->
            $scope.categories = angular.copy(results)
          event.start_date = Util.myDateTimeFormat(new Date(event.start_date))
          event.end_date = Util.myDateTimeFormat(new Date(event.end_date))
          $scope.event = angular.copy(event)
          $scope.location = angular.copy(event.location)
          $scope.contact = angular.copy(event.contact)
          $scope.category = {'name':angular.copy(event.category)}
          $scope.event.price = parseInt(angular.copy(event.price))
          $scope.event.age_limit = parseInt(angular.copy(event.age_limit))
          $scope.toggleModal()
          return
        )

    #View Event
    $scope.viewEvent = (eventId, jsEvent, view ) ->
        if(angular.isUndefined(eventId))
            return
        $scope.isnewevent = false
        event = Util.findByKey($scope.events , 'id' , eventId)
        $scope.event = angular.copy(event.details)

    $scope.eventRender = ( event, element, view ) ->
        element.attr({
                       'uib-popover-template':"'eventPopoverTemplate.html'",
                       'data-container':"body",
                       'popover-trigger':"outsideClick",
                       'popover-placement':'auto',
                       'ng-click':"viewEvent("+event.id+")"
                    })

        $compile(element)($scope)

    $scope.events = []
    $scope.monthInMilliseconds = 1000 * 60 * 60 * 24 * 30
    $scope.DayInMilliseconds = 1000 * 60 * 60 * 24 * 1
    $scope.MonthsWords = new Array();
    $scope.MonthsWords[0] = "January";
    $scope.MonthsWords[1] = "February";
    $scope.MonthsWords[2] = "March";
    $scope.MonthsWords[3] = "April";
    $scope.MonthsWords[4] = "May";
    $scope.MonthsWords[5] = "June";
    $scope.MonthsWords[6] = "July";
    $scope.MonthsWords[7] = "August";
    $scope.MonthsWords[8] = "September";
    $scope.MonthsWords[9] = "October";
    $scope.MonthsWords[10] = "November";
    $scope.MonthsWords[11] = "December";

    $scope.loadedMonths = []

    $scope.nextMonthYear = (monthyear)->
        MY = monthyear.split("-")
        month = parseInt(MY[0])
        year = parseInt(MY[1])
        month =  month + 1
        if (month >= 13 )
            month = 1
            year =  year + 1
        return month + "-" + year

    $scope.prevMonthYear = (monthyear)->
        MY = monthyear.split("-")
        month = parseInt(MY[0])
        year = parseInt(MY[1])
        month =  month - 1
        if (month <= 0 )
            month = 12
            year =  year - 1
        return month + "-" + year

    $scope.getMonthYear=(viewDate)->
        return (viewDate.getMonth() + 1) + "-" + (1900 + viewDate.getYear())

    $scope.getFirstLastDayMonth=(monthyear)->
        MY = monthyear.split("-")
        month = parseInt(MY[0])
        year = parseInt(MY[1])
        firstDay = new Date(year, month - 1, 1)
        lastDay = new Date(year, month, 0)
        days = new Array()
        days[0] = firstDay
        days[1] = lastDay
        return days


    $scope.getFormatMonthYear = (monthyear)->
        MY = monthyear.split("-")
        month = parseInt(MY[0])
        year = parseInt(MY[1])
        longMonth = $scope.MonthsWords[month-1];
        return longMonth + " " + year



    $scope.meetupEvents = (monthyear)->
          $scope.showSpinner = true
      #  if(Util.findByKey($scope.loadedMonths , 'value' , monthyear) == null)
          $http({  method: 'GET', url:'/api/users/'+ AuthUser.username + '/meetup/'+ monthyear   })
          .then((results)->
              #$log.debug("meetup Events:" + angular.toJson(results, true))
              angular.forEach(results.data, (event) ->
                    $scope.addEventToCalender(event)
              )
              $scope.AfterEventsLoaded()
              $scope.showSpinner = false
         )


    $scope.fbEvents = (monthyear)->
      #  if(Util.findByKey($scope.loadedMonths , 'value' , monthyear) == null)
          $http({  method: 'GET', url:'/api/users/'+ AuthUser.username + '/fbevents/'+ monthyear   })
          .then((results)->
              #$log.debug("Facebook Events:" + angular.toJson(results, true))
              angular.forEach(results.data, (event) ->
                    $scope.addEventToCalender(event)
              )
              $scope.AfterEventsLoaded()
         )

    $scope.googleEvents = (monthyear)->
      #  if(Util.findByKey($scope.loadedMonths , 'value' , monthyear) == null)
          $http({  method: 'GET', url:'/api/users/'+ AuthUser.username + '/gcalendar/'+ monthyear   })
          .then((results)->
              #$log.debug("Google Events:" + angular.toJson(results, true))
              angular.forEach(results.data, (event) ->
                    $scope.addEventToCalender(event)
              )
              $scope.AfterEventsLoaded()
         )

    $scope.favorateEventsfn = (monthyear)->
          $scope.showSpinner = true
      #  if(Util.findByKey($scope.loadedMonths , 'value' , monthyear) == null)
          $scope.favorateEvents = []
          $http({  method: 'GET', url:'/api/users/'+ AuthUser.username + '/favorates/' + monthyear  })
          .then((results)->
              #$log.debug("My Favourate Events:" + angular.toJson(results, true))
              angular.forEach(results.data, (event) ->
                  $scope.addEventToFavorate(event)
              )
              $scope.AfterEventsFavLoaded()
         )

    $scope.openfavarateEvents = ()->
        $scope.favorateEventsfn($scope.currentMonthYear)

    $scope.fetchEvents = (monthyear)->
        $scope.currentMonthYear = monthyear
        $scope.longMonthYear = $scope.getFormatMonthYear(monthyear)
        if(Util.findByKey($scope.loadedMonths , 'value' , monthyear) == null)
            $scope.showSpinner = true
            UserEvent.query({username:AuthUser.username,monthyear:monthyear}).$promise.then (results) ->
                #$log.debug("local Events from DB:" + angular.toJson(results, true))
                angular.forEach(results, (event) ->
                    $scope.addEventToCalender(event)
                )
                $scope.AfterEventsLoaded()
                $scope.showSpinner = false
            #$scope.meetupEvents(monthyear)
            #$scope.fbEvents(monthyear)
            #$scope.googleEvents(monthyear)
            $scope.loadedMonths.push({'value':monthyear})
            $log.debug("Fetching events for " + monthyear)
        else
            $scope.AfterEventsLoaded()



    $scope.currentDate = new Date()
    $scope.currentMonthYear = $scope.getMonthYear($scope.currentDate)
    $scope.fetchEvents($scope.currentMonthYear)

    $scope.fetchNextEvents = ()->
        currentMonthYear = $scope.nextMonthYear($scope.currentMonthYear)
        $scope.fetchEvents(currentMonthYear)


    $scope.fetchPrevEvents = ()->
        currentMonthYear = $scope.prevMonthYear($scope.currentMonthYear)
        $scope.fetchEvents(currentMonthYear)

    $scope.viewChange=(view, element) ->
       #$log.debug(" view.start ", view.start, " view.end ", view.end)
       #if the view is a month long...at least 30 days...

       #if((view.end - view.start) > ($scope.monthInMilliseconds))
       monthyear = $scope.getMonthYear(view)
       $scope.fetchEvents(monthyear)

        # $scope.fetchEvents($scope.prevMonthYear(monthyear))
        # $scope.fetchEvents($scope.nextMonthYear(monthyear))

    $scope.changeView = (date, allDay, view) ->
       if(view.name == 'agendaDay')
          $scope.addEvent(date, allDay, view)
       else
          $('#myCalendar').fullCalendar('changeView', 'agendaDay')
          $('#myCalendar').fullCalendar('gotoDate', date)



    $scope.uiConfig =
      calendar:
        slotEventOverlap: false
        minTime:'04:00:00'
        height: 900
        editable: false
        defaultView: 'agendaWeek'
        header:
          left: 'month agendaWeek agendaDay'
          center: 'title'
          right: 'today prev,next'
        dayClick:  $scope.changeView
        eventDrop: $scope.alertOnDrop
        eventResize: $scope.alertOnResize
        #eventClick: $scope.viewEvent
        eventRender: $scope.eventRender
        viewRender:$scope.viewChange



]
