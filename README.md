# TaleSpire-InternetServer
Internet Server used by the TaleSpire InternetServer Plugin for message distribution

## Purpose

The purpose of this plugin is to allow mods/plugins to communicate to the other connected clients without having to hijack/abuse the Proton network and without having to create exceptions in your computer firewall. This plugin ensures that the only connections are initiated by the clients themselves (thus no issues with firewall) and the same exchange is used to obtain messages, from the Internet Server, that others have posted. It is a polling implementation but one that does not miss messages and one that should be able to communciate despite any firewall. 

## Usage

Host the MessageServerSQL.php files on a internet hosting site and use the BuildSQLDatabase.sql file to create the necessary MySQL database table.
Note: Do not host this on your local computer unless you have ensured that remote computers can route to it, otherwise the firewall will still be a problem.
My testing was done using the free hosting package offered by www.byethost.com but the solution is not specific to that hosting site. Any site that
supports PHP 7.4 (or higher) and a MySQL database should be able to host this server.

## How It Works

The clients use HTTP GET request for all operations. Technically proper implementation would use HTTP GET for obtaining messages and HTTP POST for posting messages but the server expects both operations to be requested using HTTP GET. This makes the server code more consistent and it makes testing much easier since you can just place the corrresponding request into the webbrowser address.

To obtain messages from the server the client makes the following request:

http://host/MessageServerSQL.php?session=blah1&exclude=blah2&trans=blah3

Where session is a unique id for related messages. For example, all messages related to a particular game session would use the same id. This allows the server to be used for multiple games / groups at the same time without messages from other groups being introduced.

Where exclude is an optional parameter that excludes all messages with that where posted with the given user name. This functionality only works if the clients post messages ith unique ids (which is not mandatory).

Where trans is the transaction id from which messages should be read. Typically the first request is made with 0 which gets all historic messages. The first line of the returned content will be the last transaction id. The client typically stores this for the next request to allow getting only new messages.

To post messages to the server the client makes the following request:

http://host/MessageServerSQL.php?session=blah1&user=blah2&content=blah3

Where session is a unique id for related messages. For example, all messages related to a particular game session would use the same id. This allows the server to be used for multiple games / groups at the same time without messages from other groups being introduced.

Where user is an optional parameter that identifies the user so that client requests can filted out their own messages (using the exclude option - see above). If a user is not specified, the server assignes the user Anonymous which makes it impossible to filter your own messages out. 

Where content is the message that you want to post to the server. The message is prohibited from having any pipe characters. Other chararacters including single and double quotes are okay. The contents are url encoded on the server side before being stored to ensure characters like quotes are okay. The content is url decoded when being provided back in a get. Content is limited to MySQL long text but actually it is much smaller since the message is stored url encoded.

To perform server database maitenance, the client makes the following request:

http://host/MessageServerSQL.php

This causes the server to go through all transactions and throw out any messages that are older than 30 days.







