#
#   Created By Ivan Kavuma 2015
#   Dondenow.com
#   Event Test Fixture.
#
from django.core.urlresolvers import reverse
from django.test import TestCase
from django.contrib.auth.models  import User
from events.models import Event, Location, Category, Contact
from datetime import datetime, date, time, timedelta


import urllib
import simplejson
import requests

class EventTestCase(TestCase):
    """ Test the CRUD operation on the Event model. """

    def setUp(self):
        self.user =  User.objects.create(
                    username="JohnSmith",
                    first_name="John",
                    password="password",
                    last_name="Smith",
                    email="test@exchangeideas.com")

    def test_crud_event(self):
        #create locations
        location = Location.objects.create(
                    name = "Shall We Dance Club",
                    address = "400 North Toole Avenue",
                    city = "Tucson",
                    state = "AZ" ,
                    zip = "85701"   )

        lat , long = Location.get_coordinates(location)
        print("latitude:" + str(lat))
        print("longitude:" + str(long))

        location.latitude = lat
        location.longitude = long
        location.save()

        category = Category.objects.create(name = "Family")
        contact = Contact.objects.create(
                    value="555 555 5555",
                    contact_type = Contact.Work_Phone)

        event = Event.objects.create(
                    name = "Xmas with family",
                    user = self.user,
                    location = location,
                    category = category,
                    startDate = datetime.utcnow() + timedelta(days=3) ,
                    endDate = datetime.utcnow() + timedelta(days=3,hours=4),
                    content = "The whole family is invited.",
                    website  = "exchangeideas.com",
                    age_limit = 4,
                    price = 0.99
        )
        event.contacts.add(contact)

        event1 = Event.objects.get(user = self.user)
        self.assertEqual(str(event1.price),'0.99')
        self.assertEqual(event1.category.name,"Family")
        self.assertEqual(event1.contacts.first().value,"555 555 5555")
        self.assertEqual(event1.contacts.first().contact_type,Contact.Work_Phone)
