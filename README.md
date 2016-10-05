LYNX Technik
============

This is the [Icinga Web 2](https://github.com/Icinga/icingaweb2) module for [LYNX Technik](http://www.lynx-technik.com/) [Series 5000](http://www.lynx-technik.com/en/products/series-5000/) device monitoring. Please expect more documentation soon, for now the following screenshots should give you a slight idea of what this component could be used for when monitoring LYNX devices.

Rack visualization
------------------

As soon as you add a new Rack Controller by IP address and SNMP credentials a Perl daemon will actively poll that new rack for you. All modules will show up in the frontend:

[lynxtechnik_rack](doc/img/lynxtechnik_rack.png)


Monitoring Service definition
-----------------------------

Combine multiple modules into services, their state will be watched by [Icinga](https://www.icinga.org/). That way you can put their state on your dashboards, use then in Business Process definitions and for any kind of alarming of course. All this while still keeping control over relevant input/output controls in your [APPolo | Control](http://www.lynx-technik.com/en/products/appolo-control/) software.

[lynxtechnik_icinga_services](doc/img/lynxtechnik_icinga_services.png)

This module works fine with [Icinga 1.x](https://github.com/Icinga/icinga-core) and [Icinga 2.x](https://github.com/Icinga/icinga2). It nicely integrates with LConf or the [Icinga Director](https://github.com/Icinga/icinga2), and gives a lot of assistance in case you prefer manual configuration for your monitoring system.
