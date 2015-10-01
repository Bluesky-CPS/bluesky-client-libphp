# bluesky-client-libphp

The bluesky API connection library for PHP programmer.

Requirements
------------

- [Bluesky environment](https://github.com/Bluesky-CPS/BlueSkyLoggerCloudBINResearchVer1.0)

- [PHP 5.3 at least](http://php.net/downloads.php)

- [Apache2](http://httpd.apache.org/) 

How to
------

- prepare the environment see [here](https://github.com/Bluesky-CPS/BlueSkyLoggerCloudBINResearchVer1.0)

- clone the library here

```shell
git clone https://github.com/not001praween001/bluesky-client-libphp.git
cd bluesky-client-libphp
```

- write the code

  cli.php

  ```shell
  <?php
  require('bluesky_cli.php');
  $blueskyGateway = "http://127.0.0.1:8189";
  $bluesky_cli = new Bluesky_cli($blueskyGateway, "guest", "guest");
  $bluesky_cli->test();
  ?>
  ```

- execute

  ```shell
  php cli.php
  ```
  
***Author***: *Praween AMONTAMAVUT*
