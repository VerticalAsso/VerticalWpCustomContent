# Vertical App driver plugin
This plugin allows an external service (authenticated through an `ApiKey`) to reach out the internal WordPress database and extract interesting information out of it.

# Where to find and edit the ApiKey (Configuration)
ApiKey is registered in the `xxx_options` table of the database.
It'll be created via the admin page and it's `option_name` field will be : `"verticalapp_driver_access_apikey"`.

For now, this plugin only supports a **single ApiKey**

## Database exposed tables :
Here is a (non-exhaustive) list of exposed database tables :
* v34a_comments
* v34a_posts
* v34a_postmeta
* v34a_users
* v34a_usermeta
* v34a_em_events
* v34a_em_locations
* v34a_em_bookings
* v34a_em_tickets

## Higher level data
As we're using Events Manager WordPress plugin to provide event structure for our association needs, and as Events Manager spreads the "Event" concept over many tables of the database :
this plugin exposes 2 other more complex endpoints:
* Event card : creates an Event Card, with its title, thumbnail, date, available seats and a short description (aka "excerpt").
* Full Event record : creates a full event record, including the above Event Card, with actual event content, comments, registration guidelines and participant list.

Both of those higher order data structures were created in the PHP side to avoid VerticalApi (aka Backend) to perform too many requests to reconstruct that kind of knowledge itself.
Php plugin can do this rather efficiently and data transfer seems to be the bottleneck compared to actual data retrieval from the database.

___________________________________________

# Future roadmap !
# Events Manager control/piloting
This plugin will also provide the ability to interact with the Events Manager plugin from within the WordPress ecosystem.
This has the benefit of removing the need to :
* Perform AJAX requests from outside the website. Albeit this works kind-of OK, this is originally meant to be perform from the web browser instead of programmatically.
* Request (and refresh) wordpress authentication cookies (no need to log to the actual website anymore, the plugin has you covered !)
* Manage WordPress Nonce : for the AJAX requests to EventsManager to work, Nonce validation is performed at every call. Usually, Nonces are encoded in a hidden html field, which in turn requires the tool that uses the AJAX request to first fetch the HTML content (hence the website preprocesses the HTML, it usually takes around 2 seconds).

## New event hooks
When a new event is published for the first time (or its state changed from anything to "published"), this plugin will catch the event and send a notification to the backend server.
This notification will then be used to send PUSH notifications to every VerticalApp user and they'll be the first ones to know about a new event being published !

## Event subscription/unsubscription
This plugin will expose a new REST endpoint that'll be used to perform user subscription / unsubscription to an event.
Proper error management will also be performed, like ensuring no doubles exist or that user can actually request a subscription for this particular event (based on roles and passport levels.)

## Comment posting
Finally, we'll support comment publication for events as well, so that we totally get rid of the need for the application to be connected to the actual website.
All supported requests would go through the backend first and then this plugin.


