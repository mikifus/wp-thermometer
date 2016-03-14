# Wp Progress Thermometer Plugin #
I was looking for a plugin that would allow me to make a crowdfunding-like progress bar counter. Found nothing useful and decided to do my own. The original source of this plugin gave me the idea on how to start:
https://github.com/Sigafoos/Progress-Thermometer-Wordpress-plugin

Version 1.0

# Usage #
Once installed and activated you can go to the plugin config in the admin dashboard menu. There you see a table that will display all the created thermometers. Click New Thermometer to add a define a new thermometer.

Once save it you can display the thermometer by using the shortcode:
```shortcode
[wp_thermometer id="1"]
```
You can specify the ID in the shortcode and as well override the properties saved in the admin panel. Like this:
```shortcode
[wp_thermometer id="1" title="Custom title" percent="30"]
```

### Thermometer properties ###
List of the properties to be used in the shortcode:
- title
- subtitle
- description
- goal *(number)*
- current *(number)*
- deadline *(date with format: "yyyy-mm-dd")*
- percent *(Will not change the 'current' value accordingly)*

## To do ##
- Make the Widget work. With an option box.
- Style a bit more, specially the admin page
- Improve form validation

## UPDATES ##
#### 14/03/2016 ####
- Plugin fork upload

#### 05/02/2012 ####
- You can now actually complete the thermometer! I guess I had kind of forgotten that.
- You can enable a gif of the dancing baby when you complete it, if you want. The code is commented out and I'd prefer if you didn't use our bandwidth for it, but it's there because I like dumb things.

###### Original plugin by Dan Conley ######
- https://github.com/Sigafoos
- dan.j.conley@gmail.com
- http://blog.danconley.net/progress-thermometer-wordpress-plugin
- Originally developed for Community Beer Works: http://www.communitybeerworks.com

###### License #####
*Kopyleft*


