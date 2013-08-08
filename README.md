RandomUser
==========

http://randomuser.me

# What is RandomUser?
RandomUser is an API that provides you with a randomly generated user. These users can be used as placeholders in web mockups, and will save you time from creating your own placeholder information.

# How to use
You can use AJAX or another method to ask RandomUser for a randomly generated user. If you are using JQuery, you can paste this code in your body to get started.

    $.ajax({
      url: 'http://randomuser.me/g/',
      dataType: 'json',
      success: function(results){
        console.log(results);
      }
    });
    
# Results
The application will provide you with a JSON object that you can parse and apply to your application.

    {
      user: {
        gender: female,
        name: {
          first: Rebecca,
          last: Anderson
        },
        email: rebeccaanderson93@facebook.com,
        picture: http://randomuser.me/g/portraits/women/014.jpg,
        seed: bigSwan
      }
    }
    
# Genders
RandomUser gives you a couple ways to control the results that the system outputs. You can request a specific gender using the "gender" GET parameter. Removing returns a random value.

    http://randomuser.me/g/?gender=male
    
# Seeds
In order to speed up your application, RandomUser also allows you to always request the same information each time from the service. This is for both consistancy, and it allows your browser to cache profile photos to speed up your loading time. RandomUser accepts a "seed" parameter that will always return the same results.

    http://randomuser.me/g/?seed=foobar
(note that the seed will override the gender parameter)

# Copyright Notice

Please note that RandomUser does not own or claim to own the rights to the various photos provided. In turn, neither do you. If you see a photo that belongs to you and would like it removed from our service, please contact @arronhunt
