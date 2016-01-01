#Fijdb

Yet another PHP wrapper for MySQL. This one is good though.

Instantiate it with any number of users (avoid hard-coding your usernames etc in your class (keep it in a config file where it belongs), and make it easy to allow read-only access if you're only reading).

The idea is to use this as a base class (to take care of the connecting bollocks), and to call the generic functions from within your public api functions, so you don't hard-code your database logic in your application.
