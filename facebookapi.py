#Author Ivan Kavuma October 2016
# This module connects with the Facebook api to authenticate and get Facebook events.
# 

import facebook
import urllib
import requests
import oauth2client
from oauth2client.client import OAuth2WebServerFlow
from django.conf import settings
import httplib2
import os
import base64
from datetime import datetime,timedelta
import dateutil.parser
from random import randint
from .models import User, Event, Location, Category, Contact ,MeetupCache
from pytz import timezone
import pytz
from .util import Util

FACEBOOK_APP_ID = settings.FACEBOOK_APP_ID
FACEBOOK_APP_SECRET = settings.FACEBOOK_APP_SECRET
FACEBOOK_REDIRECT_URI = settings.FACEBOOK_REDIRECT_URI
FACEBOOK_AUTH_URI = settings.FACEBOOK_AUTH_URI
credential_dir = settings.CREDENTIALS_ROOT
FACEBOOK_CREDENTIAL_FILE = settings.FACEBOOK_CREDENTIAL_FILE

import logging
logger = logging.getLogger(__name__)


class Facebookapi():

    def _flow(self, username ):
        flow = OAuth2WebServerFlow( auth_uri = FACEBOOK_AUTH_URI+"/authorize",
                               client_id=FACEBOOK_APP_ID,
                               client_secret=FACEBOOK_APP_SECRET ,
                               scope='user_events user_friends publish_actions user_birthday',# user_friends publish_stream birthday,
                               state = base64.b64encode(username),
                               redirect_uri=FACEBOOK_REDIRECT_URI,
                               grant_type='client_credentials',
                               user_agent="Donde Now Application",
                               access_type='offline',
                               approval_prompt='force')
        return flow

    def get_authorization(self, username ):
        fbAuthurl = self._flow(username).step1_get_authorize_url()
        print "facebook auth redirect " + fbAuthurl
        return fbAuthurl

    def delete_credentials(self, username):
        try:
            user = User.objects.get(username=username)
            user.fb_access_good = False
            user.save()

        except OSError:
            pass

    def check_has_facebook_credentials(self, username):
        user = User.objects.get(username=username)
        user_categories = user.categories.all()
        if ( 39 in [cat.id for cat in user_categories] or \
             38 in [cat.id for cat in user_categories]):
            #print "Facebookapi:check_has_facebook_credentials username=",username ,' access_token=',user.fb_access_good
            return user.fb_access_good
        else:
            return True

    def storeCredentials(self,code,username):
        accessCodeUrl  = FACEBOOK_AUTH_URI + '/access_token'
        payload = {'client_id':FACEBOOK_APP_ID ,
               'client_secret':FACEBOOK_APP_SECRET ,
               'scope':'user_events',
               'access_type':'offline',
               'redirect_uri':FACEBOOK_REDIRECT_URI,
               'code':code }

        result = requests.get(accessCodeUrl , params=payload).text
        # with open(credential_path, 'w') as f:
        user = User.objects.get(username=username)
        user.fb_access_good = True
        user.fb_access_token = result
        user.save()
        #print "Facebookapi:storeCredentials Username=",username,"fb token return ",result


    def get_credentials(self,username):
        user = User.objects.get(username=username)
        tokenVals = user.fb_access_token.split('&')
        token = tokenVals[0].split('=')[1]
        expires = int(tokenVals[1].split('=')[1])
        #print "Facebookapi:get_credentials Username=",username,"Token:",token ," Expires:",expires
        return token


# {u'description': u'testing', u'rsvp_status': u'attending', u'start_time': u'2016-06-21T23:00:00-0700',

    def BirthDayToDondeEvent(self,facebookBirthday):
        print " facebook birthday => ",facebookBirthday
        return None

    def FacebookToDondeEvent(self,facebookEvent,type):

        if type == 'birth':
            return self.BirthDayToDondeEvent(facebookEvent)

        #print "via print facebookEvent => ",facebookEvent
        try:
            id = int(facebookEvent["id"])
        except:
            id = (datetime.now() - datetime(1970,randint(1,12),randint(1,28))).total_seconds()

        name = facebookEvent["name"] if facebookEvent.has_key("name") else "no event name"
        #if the creator.self is true find user by email or username.
        user = User(username = str(facebookEvent["owner"]) if facebookEvent.has_key("owner") else "No Owner",
                    first_name = "",
                    last_name = "")

        #  location. location u'location': u'12405 W Emigh Rd, Tucson, AZ 85743, USA'
        # u'place': {u'name': u'4300 E garden lane, Cottonwood, AZ 86326'}, u'id': u'503302549858424', u'name': u'Test event'}
        #print "facebookEvent location =>" ,facebookEvent["place"]
        location = Location(address="no venue yet",city="",state="",zip="")
        if facebookEvent.has_key("place"):
            if facebookEvent["place"].has_key("location"):
                location = Location(
                    address = facebookEvent["place"]["location"]["street"] if not facebookEvent["place"]["location"]["street"]  is None else "",
                    city = facebookEvent["place"]["location"]["city"] if not facebookEvent["place"]["location"]["city" ] is None else "",
                    state = facebookEvent["place"]["location"]["state"] if not facebookEvent["place"]["location"]["state"]  is None else "",
                    zip = facebookEvent["place"]["location"]["zip"] if not facebookEvent["place"]["location"]["zip"]  is None  else "",
                    country = facebookEvent["place"]["location"]["country"] if not facebookEvent["place"]["location"]["country"] is None else "",
                    latitude = facebookEvent["place"]["location"]["latitude"] if not facebookEvent["place"]["location"]["latitude"] is None else 0,
                    longitude =  facebookEvent["place"]["location"]["longitude"] if not facebookEvent["place"]["location"]["longitude"] is None else 0
                )
            else:
                address = str(facebookEvent["place"]['name']).split(",")
                try:
                    location = Location(
                        address = address[0] if not address[0] is None else "",
                        city = str(address[1]) if not str(address[1])  is None else "",
                        state = str(address[2]).split(" ")[1]  if not str(address[1]).split(" ")[1] is None else "",
                        zip = str(address[2]).split(" ")[2]  if not str(address[2]).split(" ")[2]  is None else ""
                    )
                except:
                    location = Location(address=facebookEvent["place"]['name'])
                    print facebookEvent["place"]

        category = Category.objects.get(pk=38)
        contact = Contact(name = str(facebookEvent["owner"]) if facebookEvent.has_key("owner") else "")

        poster_image = facebookEvent["cover"] if facebookEvent.has_key("cover") else ""

        start_date = Util().TimetoUTC(facebookEvent["start_time"])
        try:
            end_date = Util().TimetoUTC(facebookEvent["end_time"])
        except:
            end_date = start_date + timedelta(hours=2)

        content = facebookEvent["description"] if facebookEvent.has_key("description") else ""

        event_source =  "Facebook Event"

        website = facebookEvent["ticket_uri"]  if facebookEvent.has_key("ticket_uri") else ""
        public = (facebookEvent["visibility"] == "public") if facebookEvent.has_key("visibility") else True

        event = Event(id=id,name=name,user=user,
                    location=location,contact=contact,
                    start_date=start_date,end_date=end_date,content=content,category = category,
                    event_source=event_source,website=website,public=public,poster_image = poster_image)

        return event
