Introduction
------------

This package contains a Whois (RFC954) library for PHP. It allows a PHP program
to create a Whois object, and obtain the output of a whois query with the
`lookup` function.

The response is an array containing, at least, an element 'rawData', containing
the raw output from the whois request.

It fully supports IDNA (internationalized) domains names as defined in RFC3490,
RFC3491, RFC3492 and RFC3454.

It also supports ip/AS whois queries which are very useful to trace SPAM. You
just only need to pass the doted quad ip address or the AS (Autonomus System)
handle instead of the domain name. Limited, non-recursive support for Referral
Whois (RFC 1714/2167) is also provided.

Requirements
------------

PHPWhois2 requires PHP 8.2 or better with OpenSSL support to work properly.

Without SSL support you will not be able to query domains which do not have a
whois server but that have a https based whois.

Installation
------------
```bash
composer require gemorroj/phpwhois2
```

Example usage
-------------
```php
use PHPWhois2\Whois;

$whois = new Whois();
$result = $whois->lookup('example.com');
print_r($result);
echo Whois::showHTML($result);
```

What you can query
------------------

You can use PHPWhois2 to query domain names, ip addresses and other information
like AS, i.e, both of the following examples work:
```php
use PHPWhois2\Whois;

$whois = new Whois();
$result = $whois->lookup('example.com');

$whois = new Whois();
$result = $whois->lookup('62.97.102.115');

$whois = new Whois();
$result = $whois->lookup('AS220');
```

Using special whois server
--------------------------

Some registrars can give special access to registered whois gateways
in order to have more fine control against abusing the whois services.
The currently known whois services that offer special acccess are:

### ripe

The new ripe whois server software support some special parameters
that allow to pass the real client ip address. This feature is only
available to registered gateways. If you are registered you can use
this service when querying ripe ip addresses that way:

```php
use PHPWhois2\Whois;
use PHPWhois2\WhoisClient;
use PHPWhois2\QueryParams;

$queryParams = new QueryParams();
$queryParams->tldWhoisServer['uk'] = 'whois.ripe.net?-V{version},{ip} {query}';

$whois = new Whois(new WhoisClient($queryParams));
$result = $whois->lookup('62.97.102.115');
```

### whois.isoc.org.il
This server is also using the new ripe whois server software and
thus works the same way. If you are registered you can use this service
when querying `.il` domains that way:

```php
use PHPWhois2\Whois;
use PHPWhois2\WhoisClient;
use PHPWhois2\QueryParams;

$queryParams = new QueryParams();
$queryParams->tldWhoisServer['uk'] = 'whois.isoc.org.il?-V{version},{ip} {query}';

$whois = new Whois(new WhoisClient($queryParams));
$result = $whois->lookup('example.co.uk');
```

### whois.nic.uk

They offer what they call WHOIS2 (see http://www.nominet.org.uk/go/whois2 )
to registered users (usually Nominet members) with a higher amount of
permitted queries by hour. If you are registered you can use this service
when querying .uk domains that way:

```php
use PHPWhois2\Whois;
use PHPWhois2\WhoisClient;
use PHPWhois2\QueryParams;

$queryParams = new QueryParams();
$queryParams->tldWhoisServer['uk'] = 'whois.nic.uk:1043?{hname} {ip} {query}';

$whois = new Whois(new WhoisClient($queryParams));
$result = $whois->lookup('example.co.uk');
```

This new feature also allows you to use a different whois server than
the preconfigured or discovered one by just calling whois->useWhoisServer
and passing the tld and the server and args to use for the named tld.
For example, you could use another whois server for `.au` domains that
does not limit the number of requests (but provides no owner 
information) using this:
```php
use PHPWhois2\Whois;
use PHPWhois2\WhoisClient;
use PHPWhois2\QueryParams;

$queryParams = new QueryParams();
$queryParams->tldWhoisServer['au'] = 'whois-check.ausregistry.net.au';
// to avoid the restrictions imposed by the `.be` whois server
$queryParams->tldWhoisServer['be'] = 'whois.tucows.com';

$whois = new Whois(new WhoisClient($queryParams));
```

UTF-8
-----

PHPWhois2 will assume that all whois servers return UTF-8 encoded output,
if some whois server does not return UTF-8 data, you can pass it in
the `nonUtf8Servers` array in `QueryParams`:
```php
use PHPWhois2\Whois;
use PHPWhois2\WhoisClient;
use PHPWhois2\QueryParams;

$queryParams = new QueryParams();
$queryParams->nonUtf8Servers[] = 'br.whois-servers.net';

$whois = new Whois(new WhoisClient($queryParams));
```
