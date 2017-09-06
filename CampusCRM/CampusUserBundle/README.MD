CampusUserBundle
=================

This bundle is an extension to UserBundle. 

## Customization 
* Establish an one to one relationship between User and Contact. For the purpose of record User as Attendee, each User must have a Contact linked to it. 
* Add grid *campus_user-user-event-summary-grid* to view. This is to show events that the user have attended.
* Business unit defaults to the first Business unit in the system. It assumes there is at least one business unit available.
* Remove user group from GUI

### New validation rules
1. Linked contact first name and last name must be the same as User first name and last name.
1. Gender cannot be blank.

### New fields
1. Gender
1. Contact in User table
2. User in Contact table

# Known issues
1. When creating a new user, empty email address will force the current user to logout.