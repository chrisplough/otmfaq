# otmfaq
This is a free export of otmfaq.com.

## Why?
### Why has otmfaq.com been down?
On the 9th of June, I received notice from Amazon that the EC2 instance that otmfaq was running on was involved in a DDOS attack. When I tried to log in, I found that I had lost access to the server. Since the site stopped being sponsored last November, I've been running it on my own dime - so I've been managing the site myself. Unfortunately that means that fix-it time is limited :( and it's taken this long to get access and get the crucial exports.

### Why are you shutting otmfaq.com down?
Unfortunately, I no longer have the time to manage and maintain otmfaq.com. I reached out to a couple of other independent parties, but neither of them wanted to take the reigns. I had planned to wind it down over the coming months, but the hack of the Amazon EC2 instance accelerated everything. I really wish I could have given more notice - I hope you can accept my apology. This is an opportunity, though - that could allow others to create a new, better otmfaq.

### Why are you making it available on Github?
Rather than letting otmfaq and all the contained knowledge die - or to try an sell it to a company that cared more about profits than community, I decided to make it freely available. This way all of the knowledge shared over the last 10 years could be available to everyone. Who knows - maybe this will spawn several otmfaq-like sites, each special in their own way. In the end, the best steward of this information is you, the community. I trust you to do with it as you will.

## How
### How did you get this export if you lost access to the AWS instance?
It was a long, pain-in-the-butt process. Let's just say that my sysadmin skills have become rusty :) 

### How do I use this export?
To use it, you'll need to install the otmfaq.com web directories (everything under the otmfaq.com folder) onto a web server (the original was running on Linux) and then import the MySQL dump file (otmfaq_vb.sql.gz) into a database. Then edit the htdocs/forums/includes/config.php file to allow the vBulletin software to connect to the database. Keep in mind that if you plan to run a vBulletin forum, you'll need to ensure you have a legal license from them (https://www.vbulletin.com/).

To help you, here's some documentation:
 - vBulletin 4.1.1 manual: https://www.vbulletin.com/docs/html?manualversion=40101607
 - How to import a MySQL dump file: https://stackoverflow.com/questions/17666249/how-to-import-an-sql-file-using-the-command-line-in-mysql

Feel free to use the Wiki here on Github to share with others what you learn.

## What
### What is the meaning of life?
Damned if I know. Here's what I can tell you. Try to bring yourself and others joy. Don't hurt people. Learn from your mistakes. 

### What are you doing now?
A couple of things. First, I'm greatly simplifying my life. Over the last few years, I've made it overly complicated and full of way to many material things. I'm getting back to the core. Second, I'm working on some projects to help entrepreneurs and startups bring meaningful things into our world. The best way for us to create an impact is to work together. They're still in the early stages, but it's exciting work. Once they're available, I'll announce on http://chrisplough.com. 

## Other little bits
### Licenses
The vBulletin software and related packages are bound by their commercial license. Everything else - content, attachments and such is bound by the GNU GPLv3 license - which means that you're free to distribute and modify these files, but you need to make source available. See the LICENSE file in the main repository directory for full details.

### Keeping in touch
You can keep up with me at any of the following:
 - Website: http://chrisplough.com
 - FB: https://www.facebook.com/chrisplough
 - LinkedIn: https://www.linkedin.com/in/chrisplough
 - Twitter: https://twitter.com/chrisplough
 - Instagram: https://www.instagram.com/chrisplough/
 - Snapchat: @chrisplough

### Take care and thank you for all the support over the years :)
