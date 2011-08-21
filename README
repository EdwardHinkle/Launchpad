Introduction
============
This is a collection of scripts I use for home automation experiments. This code is most
likely not fit for general consumption, but feel free to play around with it anyway.


Temperature Sensors
===================
The server communicates to a set of temperature sensors via its serial port. Temperature
data is collected at 5-minute intervals and stored in a database. There is a munin plugin
which provides the last known temperature to the munin daemon so it can be graphed.


X10 Proxy
=========
The server in the closet is connected to the CM11A X10 controller on its serial port.
There is a great linux command line utility that handles communicating with the device over
the serial line. This script is a daemon that listens for incoming UDP packets and 
interprets them as X10 commands and passes them off to the linux command.

Dependencies:
 * A physical CM11A serial port X10 controller http://kbase.x10.com/wiki/CM11A
 * heyu CLI interface for the CM11A controller http://www.heyu.org/
 * PEAR module Service_Daemon


DHCP Server
===========
TODO


DHCP Presence Detection
=======================
TODO


License
=======
Copyright (c) 2011 Aaron Parecki

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.