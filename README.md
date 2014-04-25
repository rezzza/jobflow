Jobflow
=======
[![Build Status](https://travis-ci.org/rezzza/jobflow.png?branch=master)](https://travis-ci.org/rezzza/jobflow) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/rezzza/jobflow/badges/quality-score.png?s=85072af67a34f8a78a0cc0b22e67eb733a3263e0)](https://scrutinizer-ci.com/g/rezzza/jobflow/)

Makes batch jobs creation Simpler, Easier, Faster.

ETL pattern support thanks to : https://github.com/docteurklein/php-etl

**Warning** : This code has not been executed in production.

Introduction
------------
Have a look on the slides introduced during the sfPot Marseille on 2013-09-12 :

- https://speakerdeck.com/tyx/application-du-concept-detl-pour-faire-des-jobs-avances [French]

Usage
-----

Have a look to the examples:

- [first basic example](/examples/basic.php)
- advanced rabbitmq example:
    - [Client](/examples/placetostreet-rmq.php)
    - [Job](/examples/jobs/PlaceToStreetJob.php)
- advanced rabbitmq example with pipe:
    - [Client](/examples/github-contributor-email.php)
    - [Job](/examples/jobs/GithubEmailJob.php)

Frameworks
----------
### Symfony :
Add the following bundle in your kernel :  
`new \Rezzza\Jobflow\Plugin\SymfonyBundle\RezzzaJobflowBundle()`

Create your job with jobflow and then run the symfony commands :

```
app/console jobflow:run myJobName --transport=[php|rabbitmq]
```

If you run rabbitmq transport you should run workers:
```
app/console jobflow:worker
```

Config
------

To setup rabbitmq, in your config.yml :

```yaml
rezzza_jobflow:
    transports:
        rabbitmq:
            connections:
                jobflow:
                    host:      'localhost'
                    port:      5672
                    user:      'guest'
                    password:  'guest'
                    vhost:     '/'
```

Tests
-----

Units tests powered by atoum : https://github.com/atoum/atoum

Credits
-------

Builder Architecture heavily inspired by Symfony Form Component : https://github.com/symfony/Form

[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/rezzza/jobflow/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

