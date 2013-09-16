Jobflow
=======
[![Build Status](https://travis-ci.org/rezzza/jobflow.png?branch=master)](https://travis-ci.org/rezzza/jobflow)

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
- Symfony : https://github.com/rezzza/JobflowBundle

Tests
-----

Units tests powered by atoum : https://github.com/atoum/atoum

Credits
-------

Builder Architecture heavily inspired by Symfony Form Component : https://github.com/symfony/Form