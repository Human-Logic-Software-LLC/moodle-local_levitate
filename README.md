# Levitate
LMS plugin to access wide range of courses

# Moodle Levitate Plugin  
The Levitate plugin is an integration between LMS that allows users to explore a wide range of courses in both English and Arabic languages.

## Main features
*	A wide range of courses available in both Arabic and English along with the course metadata such as Course Duration, Description, Learning outcomes.
*	Multiple filters are available for course selection, such as Time range, Search, Keywords, Language, and Program filters.
*	Selected courses can be created as a single course with multi-activities or different courses with a single activity.
*	Background task: Course creation will be done in the background.
*	Domain subscription is required, which can be done in the plugin settings page.
*	All the course and user analytics will be provided.

## Installation
1.	Copy this levitate plugin to the local directory of your Moodle instance:  local/levitate
2.	Visit the notifications page to complete the install process.
For more information, visit documentation for installing contributed modules and plugins.

## Settings
* A new Levitate section has been added under local plugins.
* The settings page allows administrators to get the API token.
* Click on "get token" and it will ask for a username and password for the Levitate connection.
* To get the token, you need to have a user on levitate.coach with whatever credentials you want and paste them into Moodle.
* If you don’t have a user in levitate.coach, kindly contact Human Logic.

## Integration
There is one table that helps to store course form data.
1.	Coursedata: Determines what courses are selected by the user.
2.	Formdata: To store Course metadata which helps in creating courses in the Moodle instance.

# Useful links
*	 Website URL
*	Source control URL
*	Bug tracker

