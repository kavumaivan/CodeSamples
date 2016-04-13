"""
  This Class is a helper module that interfaces with Meetup API
  to get user events and return them for display to the dondenow calendar
  part of Dondenow.com

"""

import requests
from datetime import datetime,timedelta
from .models import User, Event, Location, Category, Contact , MeetupCache
import ast
from pytz import timezone
import pytz
import logging
from random import randint
from django.conf import settings
from .util import Util
#import HTMLParser

logger = logging.getLogger(__name__)

class Meetupapi():
    """ Meetup API APIs"""
    def meetupToDondeEvent(self,meetupEvent):
        try:
            id = int(meetupEvent["id"])
        except:
            id = (datetime.now() - datetime(1970,randint(1,12),randint(1,28))).total_seconds()

        name = meetupEvent["name"] if meetupEvent.has_key("name") else "no event name"
        person_name = meetupEvent["group"]["who"].split(" ")  if meetupEvent["group"].has_key("who") else ' '
        fname = person_name[0] if len(person_name) > 0 else ''
        lname = person_name[1] if len(person_name) > 1 else ''

        user = User(username = lname+"ZZZ"+fname,first_name = fname,last_name = lname)

        location = Location(address="no venue yet",city="",state="",zip="")
        if meetupEvent.has_key("venue"):
            location = Location(
                address = meetupEvent["venue"]["address_1"]  if meetupEvent["venue"].has_key("address_1") else "",
                city = meetupEvent["venue"]["city"] if meetupEvent["venue"].has_key("city") else "",
                state = meetupEvent["venue"]["state"] if meetupEvent["venue"].has_key("state") else "",
                zip = meetupEvent["venue"]["zip"] if meetupEvent["venue"].has_key("zip") else "",
                longitude = meetupEvent["venue"]["lon"]  if meetupEvent["venue"].has_key("lon") else "",
                latitude =  meetupEvent["venue"]["lat"] if meetupEvent["venue"].has_key("lat") else ""
                                )

        #category = models.ForeignKey(Category , related_name='category')
        contact = Contact(name = meetupEvent["how_to_find_us"] if meetupEvent.has_key("how_to_find_us") else "")

        start_date = datetime.fromtimestamp(int(meetupEvent["time"])/1000.0 )

        duration = int(meetupEvent["duration"]) if meetupEvent.has_key("duration") else 0

        end_date = datetime.fromtimestamp( (int(meetupEvent["time"]) + duration)/1000.0 )
        #htmlparser.unescape( )
        content = meetupEvent["description"] if meetupEvent.has_key("description") else ""

        event_source = "meetup"
        website = meetupEvent["event_url"]  if meetupEvent.has_key("event_url") else "#"
        public =  (meetupEvent["visibility"] == "public") if meetupEvent.has_key("visibility") else True

        event = Event(id=id,name=name,user=user,location=location,contact=contact,
                    start_date=start_date,end_date=end_date,content=content,
                    event_source=event_source,website=website,public=public)

        return event

    def loadEvents(self,category,response,payload):
        events = []
        if response.has_key("results"):
            for meetupEvent in response["results"]:
                event  = self.meetupToDondeEvent(meetupEvent)
                event.category = category
                events.append(event)
        else:
            logger.error("Error in request: " + self.meetupeventsUrl +str(payload) + " Reponse: " + str(response))
        return events

    def getMeetupEvents(self,userId,categories,period,monthyear,zipcode,radius=50,pageSize=10):
        status = 'upcoming' if "-" not in period.split(',')[-1] else 'past'
        payload = {
            'zip':zipcode,
            'and_text':False,
            'offset':0,
            'format':'json',
            'limited_events':False,
            'photo-host':'public',
            'page':pageSize/len(categories),
            'time':period,
            'radius':radius,
            'desc':True,
            'status':status,
            'sign':True,
            'key': settings.MEETUP_API_KEY
        }

        url = settings.MEETUP_EVENTS_URL
        events = []
        for category in categories:
            payload['category'] = category.id
            # if cache is 7 days old. delete it. .replace(tzinfo=pytz.utc)
            payload['cachebuster'] = datetime.now().isocalendar()[1]

            #look in meetup cache first
            meetupcache = MeetupCache.objects.filter(userId = userId).filter(requestHash = Util().hash(payload,monthyear))
            if len(meetupcache) > 0:
                response = ast.literal_eval(meetupcache[0].resultValue)
                events = events + self.loadEvents(category,response,payload)
                continue
            else:
                response = requests.get(url , params = payload).json()
                responseTemp = response
                events = events + self.loadEvents(category,response,payload)
                MeetupCache.objects.create(userId=userId,requestHash = Util().hash(payload,monthyear),
                                            resultValue = str(responseTemp))

        return events
